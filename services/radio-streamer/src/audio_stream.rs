use crate::backend_client::{BackendClient, ChannelInfo, GetChannelInfoError};
use actix_web::web::Bytes;
use futures::channel::mpsc;
use futures::SinkExt;
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::PlayerLoop;
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

#[derive(Debug, Clone, thiserror::Error)]
pub(crate) enum CreateAudioStreamError {
    #[error(transparent)]
    GetChannelInfoError(#[from] GetChannelInfoError),
}

struct AudioStream {
    channel_info: ChannelInfo,
    receiver: mpsc::Receiver<AudioStreamMessage>,
    player_loop: Arc<Mutex<PlayerLoop<Arc<BackendClient>>>>,
    handle: JoinHandle<()>,
}

impl AudioStream {
    pub(crate) async fn create(
        channel_id: &u32,
        output_format: &OutputFormat,
        backend_client: &Arc<BackendClient>,
    ) -> Result<Self, CreateAudioStreamError> {
        let initial_time = SystemTime::now() - START_BUFFER_TIME;
        let (mut sender, receiver) = mpsc::channel(0);

        let channel_info = backend_client
            .get_channel_info(&(channel_id as usize), None)
            .await?;

        let backend_client = backend_client.clone();
        let player_loop = Arc::new(Mutex::new(PlayerLoop::create(
            *channel_id,
            backend_client,
            output_format.clone(),
            initial_time.clone(),
        )?));

        let handle = std::thread::spawn({
            move || {
                let mut previous_title = String::new();

                loop {
                    let mut lock = player_loop.lock().unwrap();

                    match lock.receive_next_audio_packets() {
                        Ok(packets) => packets,
                        Err(error) => {
                            error!(?error, "Closing the player loop on reading audio packets");
                            return;
                        }
                    }

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
                                warn!("Duration between two audio packets is too long: {dur}");
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
