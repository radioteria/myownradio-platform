use crate::audio_formats::AudioFormat;
use crate::backend_client::BackendClient;
use crate::metrics::Metrics;
use crate::stream::channel_player::{ChannelPlayer, ChannelPlayerMessage};
use crate::stream::ffmpeg_encoder::{make_ffmpeg_encoder, EncoderError};
use crate::stream::player_loop::{make_player_loop, PlayerLoopError, PlayerLoopMessage};
use crate::stream::player_registry::PlayerRegistry;
use crate::stream::types::DecodedBuffer;
use actix_rt::task::JoinHandle;
use actix_web::web::Bytes;
use futures::channel::{mpsc, oneshot};
use futures::{join, SinkExt, StreamExt, TryStreamExt};
use slog::{debug, o, Logger};
use std::sync::{Arc, Mutex, RwLock};
use std::time::Duration;

#[derive(Debug)]
pub(crate) enum ChannelEncoderError {
    EncoderError(EncoderError),
    Unexpected,
}

#[derive(Clone, Debug)]
pub(crate) enum ChannelEncoderMessage {
    TrackTitle(String),
    EncodedBuffer(Bytes),
}

#[derive(Clone)]
pub(crate) struct ChannelEncoder {
    inner: Arc<Inner>,
}

impl ChannelEncoder {
    pub async fn create<F>(
        channel_player: &ChannelPlayer,
        audio_format: &AudioFormat,
        path_to_ffmpeg: &str,
        logger: &Logger,
        metrics: Arc<Metrics>,
        on_all_receivers_disconnected: F,
    ) -> Result<Self, ChannelEncoderError>
    where
        F: 'static + Fn() -> () + Send + Sync,
    {
        let inner = Inner::create(
            channel_player,
            audio_format,
            path_to_ffmpeg,
            logger,
            metrics,
            on_all_receivers_disconnected,
        )
        .await?;

        Ok(Self { inner })
    }

    pub fn create_receiver(&self) -> mpsc::Receiver<ChannelEncoderMessage> {
        let (tx, rx) = mpsc::channel(0);

        actix_rt::spawn({
            let senders = self.inner.senders.clone();

            async move {
                let mut senders = senders.lock().unwrap();

                senders.push(tx);
            }
        });

        rx
    }

    pub fn get_channel_title(&self) -> Option<String> {
        self.inner.channel_player.get_channel_title()
    }

    pub fn get_track_title(&self) -> Option<String> {
        self.inner.channel_player.get_track_title()
    }
}

struct Inner {
    logger: Logger,
    channel_player: ChannelPlayer,
    senders: Arc<Mutex<Vec<mpsc::Sender<ChannelEncoderMessage>>>>,
    handle: Mutex<Option<JoinHandle<()>>>,
    on_all_receivers_disconnected: Box<dyn Fn() -> () + Send + Sync>,
}

impl Drop for Inner {
    fn drop(&mut self) {
        if let Some(handle) = self.handle.lock().unwrap().take() {
            handle.abort();
        }

        debug!(self.logger, "Channel player has been destroyed");
    }
}

impl Inner {
    pub async fn create<F>(
        channel_player: &ChannelPlayer,
        audio_format: &AudioFormat,
        path_to_ffmpeg: &str,
        logger: &Logger,
        metrics: Arc<Metrics>,
        on_all_receivers_disconnected: F,
    ) -> Result<Arc<Self>, ChannelEncoderError>
    where
        F: 'static + Fn() -> () + Send + Sync,
    {
        let senders: Arc<Mutex<Vec<mpsc::Sender<_>>>> = Arc::default();
        let handle: Mutex<Option<_>> = Mutex::default();
        let on_all_receivers_disconnected = Box::new(on_all_receivers_disconnected);

        let logger = logger.clone();

        let (encoder_sender, encoder_receiver) =
            make_ffmpeg_encoder(audio_format, path_to_ffmpeg, &logger, &metrics)
                .map_err(|error| ChannelEncoderError::EncoderError(error))?;

        let inner = Arc::new(Self {
            logger,
            senders,
            channel_player: channel_player.clone(),
            handle,
            on_all_receivers_disconnected,
        });

        let handle = actix_rt::spawn({
            let mut encoder_receiver = encoder_receiver;
            let mut encoder_sender = encoder_sender;
            let mut channel_player_messages = channel_player.create_receiver();

            let inner = Arc::downgrade(&inner);

            let input = async move {
                while let Some(message) = channel_player_messages.next().await {
                    match message {
                        ChannelPlayerMessage::TimedBuffer(DecodedBuffer(bytes, _)) => {
                            if let Err(error) = encoder_sender.send(bytes).await {
                                break;
                            }
                        }
                        _ => (),
                    }
                }
            };

            let output = async move {
                while let Some(bytes) = encoder_receiver.next().await {
                    if let Some(inner) = inner.upgrade() {
                        inner
                            .send_all(ChannelEncoderMessage::EncodedBuffer(bytes))
                            .await
                    }
                }
            };

            async move {
                join!(input, output);
            }
        });

        inner.handle.lock().unwrap().replace(handle);

        Ok(inner)
    }

    async fn send_all(&self, event: ChannelEncoderMessage) {
        let logger = self.logger.clone();

        let mut has_disconnected_senders = false;

        for sender in self.senders.lock().unwrap().iter_mut() {
            if let Err(_) = sender.send(event.clone()).await {
                debug!(logger, "Unable to send message: channel closed");
                has_disconnected_senders = true;
            }
        }

        if has_disconnected_senders {
            debug!(logger, "Performing retain");

            let mut senders = self.senders.lock().unwrap();

            senders.retain(|sender| !sender.is_closed());

            if senders.len() == 0 {
                (self.on_all_receivers_disconnected)();
            }
        }
    }
}
