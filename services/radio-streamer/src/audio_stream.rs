use crate::backend_client::{
    BackendClient, ChannelInfo, GetChannelInfoError, GetNowPlayingError, NowPlaying,
};
use crate::streams_registry::StreamsRegistry;
use actix_web::web::Bytes;
use futures::SinkExt;
use myownradio_channel_utils::{Channel, ChannelClosed, TimedChannel};
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::{
    NowPlayingClient, NowPlayingError, NowPlayingResponse, PlayerLoop, PlayerLoopError,
};
use std::sync::{mpsc, Arc, Mutex};
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

#[derive(Clone)]
pub(crate) struct AudioStream {
    inner: Arc<Inner>,
}

impl AudioStream {
    pub(crate) async fn create(
        channel_id: &u32,
        output_format: &OutputFormat,
        backend_client: &BackendClient,
        streams_registry: &StreamsRegistry,
    ) -> Result<Self, CreateAudioStreamError> {
        let inner = Arc::new(
            Inner::create(channel_id, output_format, backend_client, streams_registry).await?,
        );

        Ok(Self { inner })
    }

    pub(crate) fn restart(&self) {
        self.inner.restart();
    }

    pub(crate) fn subscribe(
        &self,
    ) -> Result<impl Iterator<Item = AudioStreamMessage>, ChannelClosed> {
        self.inner.subscribe()
    }
}

struct Inner {
    channel_info: ChannelInfo,
    channel: TimedChannel<AudioStreamMessage>,
    player_loop: Arc<Mutex<PlayerLoop<BackendClient>>>,
    handle: JoinHandle<()>,
}

impl Inner {
    async fn create(
        channel_id: &u32,
        output_format: &OutputFormat,
        backend_client: &BackendClient,
        streams_registry: &StreamsRegistry,
    ) -> Result<Self, CreateAudioStreamError> {
        let channel_id = *channel_id;

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
        let channel = TimedChannel::new(Duration::from_secs(5), 16);

        let channel_info = backend_client
            .get_channel_info(&(channel_id as usize), None)
            .await?;

        let backend_client = backend_client.clone();
        let player_loop = Arc::new(Mutex::new(PlayerLoop::create(
            channel_id,
            backend_client,
            output_format.clone(),
            initial_time.clone(),
        )?));

        let handle = std::thread::spawn({
            let player_loop = player_loop.clone();
            let channel = channel.clone();
            let streams_registry = streams_registry.clone();
            let output_format = output_format.clone();

            move || {
                scopeguard::defer!(streams_registry.unregister(channel_id, output_format));

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
                            if channel
                                .send(AudioStreamMessage::TrackTitle(title.clone()))
                                .is_err()
                            {
                                error!("Closing the player loop on sending AudioStreamMessage::TrackTitle");
                                return;
                            };
                            previous_title = title;
                        }
                    }

                    for packet in packets {
                        let bytes = Bytes::copy_from_slice(&packet.data());

                        if channel.send(AudioStreamMessage::Bytes(bytes)).is_err() {
                            error!("Closing the player loop on sending AudioStreamMessage::Bytes");
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
            channel,
            handle,
            channel_info,
            player_loop,
        })
    }

    fn restart(&self) {
        self.player_loop.lock().unwrap().restart();
    }

    fn subscribe(&self) -> Result<impl Iterator<Item = AudioStreamMessage>, ChannelClosed> {
        self.channel.subscribe()
    }
}
