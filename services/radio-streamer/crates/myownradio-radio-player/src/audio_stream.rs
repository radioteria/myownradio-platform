use crate::backend_client::{BackendClient, ChannelInfo, GetChannelInfoError};
use crate::metrics::Metrics;
use crate::types::ChannelId;
use actix_rt::task::JoinHandle;
use actix_web::web::Bytes;
use futures::lock::Mutex;
use futures::Stream;
use myownradio_channel_utils::{Channel, ChannelClosed, ReplayChannel, TimedChannel};
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::{PlayerLoop, PlayerLoopError};
use scopeguard::defer;
use std::ops::Deref;
use std::sync::Arc;
use std::time::{Duration, SystemTime};
use tracing::{error, warn};

const START_BUFFER_TIME: Duration = Duration::from_millis(2500);
const MAX_DURATION_BETWEEN_PACKETS: Duration = Duration::from_secs(1);

#[derive(Debug, Clone)]
pub(crate) enum AudioStreamMessage {
    Buffer { bytes: Bytes, pts: Duration },
    TrackTitle { title: String, pts: Duration },
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateAudioStreamError {
    #[error("GetChannelInfoError: {0:?}")]
    GetChannelInfoError(#[from] GetChannelInfoError),
    #[error("PlayerLoopError: {0:?}")]
    PlayerLoopError(#[from] PlayerLoopError),
}

pub(crate) struct AudioStream {
    channel_info: ChannelInfo,
    channel: Box<dyn Channel<AudioStreamMessage> + Sync + Send>,
    player_loop: Arc<Mutex<PlayerLoop<BackendClient>>>,
    async_handle: JoinHandle<()>,
}

impl AudioStream {
    pub(crate) async fn create(
        channel_id: &ChannelId,
        output_format: &OutputFormat,
        backend_client: &BackendClient,
        metrics: &Metrics,
    ) -> Result<Self, CreateAudioStreamError> {
        let channel_info = backend_client
            .get_channel_info(&channel_id.clone().into(), None)
            .await?;

        let initial_time = SystemTime::now() - START_BUFFER_TIME;
        let channel = TimedChannel::<AudioStreamMessage>::new(Duration::from_secs(30), 16);
        let channel = ReplayChannel::new(channel, START_BUFFER_TIME);

        let backend_client = backend_client.clone();
        let player_loop = PlayerLoop::create(
            *channel_id.deref(),
            backend_client,
            output_format.clone(),
            initial_time,
        )?;
        let player_loop = Arc::new(Mutex::new(player_loop));

        let async_handle = actix_rt::spawn({
            let player_loop = player_loop.clone();
            let channel = channel.clone();
            let metrics = metrics.clone();

            async move {
                metrics.inc_active_player_loops();

                defer!(metrics.dec_active_player_loops());

                let mut previous_title = String::new();

                loop {
                    let mut lock = player_loop.lock().await;

                    let packets = match lock.process_next_audio_packets().await {
                        Ok(packets) => packets,
                        Err(error) => {
                            error!(?error, "Closing the player loop on reading audio packets");
                            return;
                        }
                    };

                    if let Some(title) = lock.current_title() {
                        if title != &previous_title {
                            let title = String::from(title);
                            let msg = AudioStreamMessage::TrackTitle {
                                title: title.clone(),
                                pts: *lock.current_running_time(),
                            };

                            if channel.send(msg).await.is_err() {
                                error!("Closing the player loop on sending AudioStreamMessage::TrackTitle");
                                return;
                            };

                            previous_title = title;
                        }
                    }

                    drop(lock);

                    for packet in packets {
                        let msg = AudioStreamMessage::Buffer {
                            bytes: Bytes::copy_from_slice(&packet.data()),
                            pts: packet.pts_as_duration(),
                        };

                        if channel.send(msg).await.is_err() {
                            error!("Closing the player loop on sending AudioStreamMessage::Buffer");
                            return;
                        }

                        let sleep_dur = (initial_time + packet.pts_as_duration())
                            .duration_since(SystemTime::now())
                            .ok();

                        if let Some(dur) = sleep_dur {
                            if dur > MAX_DURATION_BETWEEN_PACKETS {
                                warn!("Duration between two audio packets is too long: {dur:?}");
                            }

                            actix_rt::time::sleep(dur).await;
                        }
                    }
                }
            }
        });

        let channel = Box::new(channel);

        Ok(Self {
            channel,
            channel_info,
            player_loop,
            async_handle,
        })
    }

    pub(crate) async fn restart(&self) {
        self.player_loop.lock().await.restart();
    }

    pub(crate) fn subscribe(
        &self,
    ) -> Result<impl Stream<Item = AudioStreamMessage>, ChannelClosed> {
        self.channel.subscribe()
    }

    pub(crate) fn channel_info(&self) -> &ChannelInfo {
        &self.channel_info
    }

    pub(crate) async fn current_title(&self) -> Option<String> {
        self.player_loop
            .lock()
            .await
            .current_title()
            .map(ToString::to_string)
    }
}

impl Drop for AudioStream {
    fn drop(&mut self) {
        self.async_handle.abort();
    }
}
