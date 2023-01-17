use super::streams_registry::StreamsRegistry;
use super::timed_channel::{ChannelError, TimedChannel};
use crate::audio_formats::AudioFormat;
use crate::backend_client::{BackendClient, ChannelInfo, MorBackendClientError};
use crate::metrics::Metrics;
use crate::stream::constants::PRELOAD_TIME;
use crate::stream::ffmpeg::EncoderOutput;
use crate::stream::player_loop::{make_player_loop, PlayerLoopMessage};
use crate::stream::replay_timed_channel::{ReplayTimedChannel, TimedMessage};
use crate::stream::types::{Buffer, TrackTitle};
use crate::stream::{build_ffmpeg_encoder, EncoderError};
use crate::upgrade_weak;
use actix_rt::task::JoinHandle;
use futures::channel::oneshot;
use futures::{stream, SinkExt, StreamExt};
use slog::{info, Logger};
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};
use std::time::Duration;

#[derive(Debug, Clone)]
pub(crate) enum StreamMessage {
    Buffer(Buffer),
    TrackTitle(TrackTitle),
}

impl TimedMessage for StreamMessage {
    fn pts(&self) -> &Duration {
        match self {
            StreamMessage::Buffer(b) => b.pts_hint(),
            StreamMessage::TrackTitle(t) => t.pts_hint(),
        }
    }
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum GetOutputError {
    #[error(transparent)]
    ChannelError(#[from] ChannelError),
    #[error(transparent)]
    EncoderError(#[from] EncoderError),
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum StreamCreateError {
    #[error("Channel not found")]
    ChannelNotFound,
    #[error(transparent)]
    BackendError(#[from] MorBackendClientError),
}

#[derive(Debug)]
pub(crate) enum StopReason {
    NoConsumers,
    PlayerStopped,
}

pub(crate) struct Stream {
    // Dependencies
    logger: Logger,
    streams_registry: Arc<StreamsRegistry>,
    outputs: StreamOutputs,
    // Static state
    channel_id: usize,
    channel_info: ChannelInfo,
    // Dynamic state
    restart_sender: Arc<Mutex<Option<oneshot::Sender<()>>>>,
    track_title: Arc<Mutex<String>>,
    player_loop_handle: JoinHandle<()>,
}

impl Stream {
    pub(crate) async fn create(
        channel_id: &usize,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
        streams_registry: Arc<StreamsRegistry>,
    ) -> Result<Self, StreamCreateError> {
        let stream_messages_channel = Arc::new(ReplayTimedChannel::new(
            TimedChannel::new(Duration::from_secs(5), 0),
            PRELOAD_TIME,
        ));
        let restart_sender = Arc::new(Mutex::new(None));
        let track_title = Arc::new(Mutex::new(String::default()));

        let channel_info = match backend_client.get_channel_info(&channel_id, None).await {
            Ok(channel_info) => channel_info,
            Err(MorBackendClientError::ChannelNotFound) => {
                return Err(StreamCreateError::ChannelNotFound);
            }
            Err(error) => {
                return Err(StreamCreateError::BackendError(error));
            }
        };

        let player_loop_handle = actix_rt::spawn({
            let channel_id = channel_id.clone();
            let mut player_messages =
                make_player_loop(&channel_id, backend_client, logger, metrics);

            let restart_sender = restart_sender.clone();
            let stream_messages_channel = stream_messages_channel.clone();
            let track_title = track_title.clone();
            let streams_registry = Arc::downgrade(&streams_registry);

            async move {
                while let Some(msg) = player_messages.next().await {
                    let result = match msg {
                        PlayerLoopMessage::DecodedBuffer(buffer) => {
                            stream_messages_channel
                                .send_all(StreamMessage::Buffer(buffer))
                                .await
                        }
                        PlayerLoopMessage::TrackTitle(title) => {
                            *track_title.lock().unwrap() = title.title().to_string();
                            stream_messages_channel
                                .send_all(StreamMessage::TrackTitle(title))
                                .await
                        }
                        PlayerLoopMessage::RestartSender(sender) => {
                            restart_sender.lock().unwrap().replace(sender);
                            Ok(())
                        }
                    };

                    if result.is_err() {
                        upgrade_weak!(streams_registry)
                            .get_stream(&channel_id)
                            .map(|s| s.stop(StopReason::NoConsumers));
                        return;
                    }
                }

                restart_sender.lock().unwrap().take();
                track_title.lock().unwrap().clear();

                upgrade_weak!(streams_registry)
                    .get_stream(&channel_id)
                    .map(|s| s.stop(StopReason::PlayerStopped));
            }
        });

        let outputs = StreamOutputs {
            stream_messages_channel,
            logger: logger.clone(),
            metrics: metrics.clone(),
            outputs: Arc::new(Mutex::default()),
        };

        Ok(Self {
            channel_id: *channel_id,
            channel_info,
            logger: logger.clone(),
            streams_registry,
            track_title,
            restart_sender,
            player_loop_handle,
            outputs,
        })
    }

    pub(crate) fn stop(&self, reason: StopReason) {
        info!(
            self.logger,
            "Player stopped (channel_id={}, reason={:?})", self.channel_id, reason
        );

        self.player_loop_handle.abort();

        self.streams_registry
            .unregister_stream(&self.channel_id, reason);
    }

    pub(crate) fn channel_info(&self) -> ChannelInfo {
        self.channel_info.clone()
    }

    pub(crate) fn track_title(&self) -> String {
        self.track_title.lock().unwrap().clone()
    }

    pub(crate) fn restart(&self) {
        if let Some(restart_sender) = self.restart_sender.lock().unwrap().take() {
            let _ = restart_sender.send(());
        }
    }

    pub(crate) fn get_output(
        &self,
        format: &AudioFormat,
    ) -> Result<impl stream::Stream<Item = StreamMessage>, GetOutputError> {
        self.outputs.get_output(format)
    }
}

struct StreamOutputs {
    stream_messages_channel: Arc<ReplayTimedChannel<StreamMessage>>,
    metrics: Metrics,
    logger: Logger,
    outputs: Arc<Mutex<HashMap<AudioFormat, Arc<ReplayTimedChannel<StreamMessage>>>>>,
}

impl StreamOutputs {
    pub(crate) fn get_output(
        &self,
        format: &AudioFormat,
    ) -> Result<impl stream::Stream<Item = StreamMessage>, GetOutputError> {
        match self.outputs.lock().unwrap().entry(format.clone()) {
            Entry::Occupied(entry) => Ok(entry.get().create_receiver()?),
            Entry::Vacant(entry) => {
                let (mut encoder_sender, mut encoder_receiver) =
                    build_ffmpeg_encoder(format, &self.logger, &self.metrics)?;
                let mut receiver = self.stream_messages_channel.create_receiver()?;

                actix_rt::spawn({
                    async move {
                        while let Some(msg) = receiver.next().await {
                            if let StreamMessage::Buffer(bytes) = msg {
                                if encoder_sender.send(bytes).await.is_err() {
                                    break;
                                }
                            }
                        }
                    }
                });

                let encoded_messages_channel = Arc::new(ReplayTimedChannel::new(
                    TimedChannel::new(Duration::from_secs(10), 32),
                    PRELOAD_TIME,
                ));
                let receiver = encoded_messages_channel.create_receiver()?;

                actix_rt::spawn({
                    let encoded_messages_channel = encoded_messages_channel.clone();
                    let outputs = self.outputs.clone();
                    let format = format.clone();

                    async move {
                        while let Some(output) = encoder_receiver.next().await {
                            match output {
                                EncoderOutput::Buffer(buffer) => {
                                    if encoded_messages_channel
                                        .send_all(StreamMessage::Buffer(buffer))
                                        .await
                                        .is_err()
                                    {
                                        // All consumers has been disconnected.
                                        break;
                                    }
                                }
                                EncoderOutput::EOF => {
                                    // Received end of output.
                                    break;
                                }
                                EncoderOutput::Error(_exit_code) => {
                                    // Unexpectedly terminated encoder process.
                                    break;
                                }
                            }
                        }

                        outputs.lock().unwrap().remove(&format);
                    }
                });

                entry.insert(encoded_messages_channel);

                Ok(receiver)
            }
        }
    }
}
