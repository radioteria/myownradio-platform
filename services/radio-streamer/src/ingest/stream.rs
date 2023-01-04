use crate::audio_formats::AudioFormat;
use crate::backend_client::BackendClient;
use crate::ingest::timed_channel::{ChannelError, TimedChannel};
use crate::metrics::Metrics;
use crate::stream::ffmpeg_encoder::{make_ffmpeg_encoder, EncoderError};
use crate::stream::player_loop::{make_player_loop, PlayerLoopMessage};
use crate::stream::types::Buffer;
use actix_web::web::Bytes;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use slog::Logger;
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};
use std::time::Duration;

#[derive(Debug, Clone)]
pub(crate) enum StreamMessage {
    BufferBytes(Bytes),
    TrackTitle(String),
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum GetFormatError {
    #[error(transparent)]
    ChannelError(#[from] ChannelError),
    #[error(transparent)]
    EncoderError(#[from] EncoderError),
}

pub(crate) struct Stream {
    // Dependencies
    logger: Logger,
    metrics: Metrics,
    path_to_ffmpeg: &'static str,
    // Static state
    channel_id: usize,
    stream_messages_channel: TimedChannel<StreamMessage>,
    // Dynamic state
    restart_sender: Arc<Mutex<Option<oneshot::Sender<()>>>>,
    track_title: Arc<Mutex<String>>,
    encoders_map: Arc<Mutex<HashMap<AudioFormat, TimedChannel<StreamMessage>>>>,
}

impl Stream {
    pub(crate) async fn create(
        channel_id: &usize,
        path_to_ffmpeg: &'static str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
    ) -> Self {
        let stream_messages_channel = TimedChannel::new(Duration::from_secs(5), 0);
        let restart_sender = Arc::new(Mutex::new(None));
        let track_title = Arc::new(Mutex::new(String::default()));
        let encoders_map = Arc::new(Mutex::new(HashMap::<
            AudioFormat,
            TimedChannel<StreamMessage>,
        >::new()));

        actix_rt::spawn({
            let mut player_messages =
                make_player_loop(&channel_id, path_to_ffmpeg, backend_client, logger, metrics);

            let restart_sender = restart_sender.clone();
            let stream_messages_channel = stream_messages_channel.clone();
            let track_title = track_title.clone();

            async move {
                while let Some(msg) = player_messages.next().await {
                    let result = match msg {
                        PlayerLoopMessage::DecodedBuffer(buffer) => {
                            stream_messages_channel
                                .send_all(StreamMessage::BufferBytes(buffer.into_bytes()))
                                .await
                        }
                        PlayerLoopMessage::TrackTitle(title) => {
                            *track_title.lock().unwrap() = title.clone();
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
                        // @todo Logging
                        break;
                    }
                }

                restart_sender.lock().unwrap().take();
                track_title.lock().unwrap().clear();

                // @todo Close stream
            }
        });

        Self {
            channel_id: *channel_id,
            stream_messages_channel,
            path_to_ffmpeg,
            logger: logger.clone(),
            metrics: metrics.clone(),
            track_title,
            restart_sender,
            encoders_map,
        }
    }

    pub(crate) fn get_format(
        &self,
        format: AudioFormat,
    ) -> Result<mpsc::Receiver<StreamMessage>, GetFormatError> {
        match self.encoders_map.lock().unwrap().entry(format.clone()) {
            Entry::Occupied(entry) => Ok(entry.get().create_receiver()?),
            Entry::Vacant(entry) => {
                let (encoder_sink, encoder_src) = self.make_encoder(&format)?;

                actix_rt::spawn({
                    let mut receiver = self.stream_messages_channel.create_receiver()?;
                    let mut encoder_sink = encoder_sink;

                    async move {
                        while let Some(msg) = receiver.next().await {
                            if let StreamMessage::BufferBytes(bytes) = msg {
                                let result = encoder_sink.send(bytes).await;
                                if let Err(result) = result {
                                    // @todo Close stream with reason EncoderError
                                    break;
                                }
                            }
                        }
                    }
                });

                let encoded_messages_channel = TimedChannel::new(Duration::from_secs(10), 32);
                let receiver = encoded_messages_channel.create_receiver()?;

                actix_rt::spawn({
                    let mut encoder_src = encoder_src;

                    let encoded_messages_channel = encoded_messages_channel.clone();
                    let encoders_map = self.encoders_map.clone();

                    async move {
                        while let Some(bytes) = encoder_src.next().await {
                            if encoded_messages_channel
                                .send_all(StreamMessage::BufferBytes(bytes))
                                .await
                                .is_err()
                            {
                                // All consumers has been disconnected.
                                break;
                            }
                        }

                        encoders_map.lock().unwrap().remove(&format);
                    }
                });

                entry.insert(encoded_messages_channel);

                Ok(receiver)
            }
        }
    }

    pub(crate) fn close(&self) {}

    fn make_encoder(
        &self,
        format: &AudioFormat,
    ) -> Result<(mpsc::Sender<Bytes>, mpsc::Receiver<Bytes>), EncoderError> {
        make_ffmpeg_encoder(format, self.path_to_ffmpeg, &self.logger, &self.metrics)
    }
}
