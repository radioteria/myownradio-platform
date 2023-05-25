use crate::backend_client::{
    BackendClient, ChannelInfo, GetChannelInfoError, GetNowPlayingError, NowPlaying,
};
use actix_web::web::Bytes;
use futures::channel::mpsc;
use futures::SinkExt;
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::{
    NowPlayingClient, NowPlayingError, NowPlayingResponse, PlayerLoop, PlayerLoopError,
};
use std::sync::{Arc, Mutex};
use std::thread::JoinHandle;
use std::time::{Duration, SystemTime};
use tracing::{error, warn};

const START_BUFFER_TIME: Duration = Duration::from_millis(2500);
const MAX_DURATION_BETWEEN_PACKETS: Duration = Duration::from_secs(1);

#[derive(Debug, Clone)]
pub(crate) enum AudioStreamMessage {
    Bytes(Bytes),
    TrackTitle(String),
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateAudioStreamError {
    #[error("GetChannelInfoError: {0:?}")]
    GetChannelInfoError(#[from] GetChannelInfoError),
    #[error("PlayerLoopError: {0:?}")]
    PlayerLoopError(#[from] PlayerLoopError),
}

struct AudioStream {
    channel_info: ChannelInfo,
    receiver: mpsc::Receiver<AudioStreamMessage>,
    player_loop: Arc<Mutex<PlayerLoop<BackendClient>>>,
    handle: JoinHandle<()>,
}

impl AudioStream {
    pub(crate) async fn create(
        channel_id: &u32,
        output_format: &OutputFormat,
        backend_client: &BackendClient,
    ) -> Result<Self, CreateAudioStreamError> {
        impl NowPlayingResponse for NowPlaying {
            fn curr_url(&self) -> &str {
                &self.current_track.url
            }

            fn curr_title(&self) -> &str {
                &self.current_track.title
            }

            fn curr_duration(&self) -> &Duration {
                &self.current_track.duration
            }

            fn curr_position(&self) -> &Duration {
                &self.current_track.offset
            }
        }

        impl NowPlayingError for GetNowPlayingError {}

        impl NowPlayingClient for BackendClient {
            fn get_now_playing(
                &self,
                channel_id: &u32,
                time: &SystemTime,
            ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>> {
                let runtime = actix_rt::Runtime::new().expect("Unable to create Runtime");

                let channel_id = *channel_id as usize;

                let future = BackendClient::get_now_playing(self, &channel_id, time);

                runtime
                    .block_on(future)
                    .map(|value| Box::new(value) as Box<dyn NowPlayingResponse>)
                    .map_err(|error| Box::new(error) as Box<dyn NowPlayingError>)
            }
        }

        let initial_time = SystemTime::now() - START_BUFFER_TIME;
        let (mut sender, receiver) = mpsc::channel(0);

        let channel_info = backend_client
            .get_channel_info(&(*channel_id as usize), None)
            .await?;

        let backend_client = backend_client.clone();
        let player_loop = Arc::new(Mutex::new(PlayerLoop::create(
            *channel_id,
            backend_client,
            output_format.clone(),
            initial_time.clone(),
        )?));

        let handle = std::thread::spawn({
            let player_loop = player_loop.clone();

            move || {
                let mut previous_title = String::new();

                loop {
                    let mut lock = player_loop.lock().unwrap();

                    let packets = match lock.receive_next_audio_packets() {
                        Ok(packets) => packets,
                        Err(error) => {
                            error!(?error, "Closing the player loop on reading audio packets");
                            return;
                        }
                    };

                    if let Some(title) = lock.current_title() {
                        if title != &previous_title {
                            let title = String::from(title);
                            if let Err(error) =
                                sender.try_send(AudioStreamMessage::TrackTitle(title.clone()))
                            {
                                error!(?error, "Closing the player loop on sending AudioStreamMessage::TrackTitle");
                                return;
                            };
                            previous_title = title;
                        }
                    }

                    for packet in packets {
                        let bytes = Bytes::copy_from_slice(&packet.data());
                        if let Err(error) = sender.try_send(AudioStreamMessage::Bytes(bytes)) {
                            error!(
                                ?error,
                                "Closing the player loop on sending AudioStreamMessage::Bytes"
                            );
                            return;
                        }

                        let sleep_dur = (initial_time + packet.pts_as_duration())
                            .duration_since(SystemTime::now())
                            .ok();

                        if let Some(dur) = sleep_dur {
                            if dur > MAX_DURATION_BETWEEN_PACKETS {
                                warn!("Duration between two audio packets is too long: {dur:?}");
                            }

                            std::thread::sleep(dur.min(MAX_DURATION_BETWEEN_PACKETS));
                        }
                    }
                }
            }
        });

        Ok(Self {
            receiver,
            handle,
            channel_info,
            player_loop,
        })
    }

    pub(crate) fn restart(&self) {
        self.player_loop.lock().unwrap().restart();
    }
}
