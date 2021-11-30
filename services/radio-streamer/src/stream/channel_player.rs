use crate::backend_client::BackendClient;
use crate::metrics::Metrics;
use crate::stream::player_loop::{make_player_loop, PlayerLoopError, PlayerLoopMessage};
use crate::stream::types::TimedBuffer;
use actix_rt::task::JoinHandle;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use slog::{debug, o, Logger};
use std::sync::{Arc, Mutex, RwLock};
use std::time::Duration;

pub(crate) const PLAYER_IDLE_TIMEOUT: Duration = Duration::from_secs(30);

#[derive(Debug)]
pub(crate) enum ChannelPlayerError {
    PlayerLoopError(PlayerLoopError),
}

#[derive(Clone, Debug)]
pub(crate) enum ChannelPlayerMessage {
    ChannelTitle(String),
    TrackTitle(String),
    TimedBuffer(TimedBuffer),
}

#[derive(Clone)]
pub(crate) struct ChannelPlayer {
    inner: Arc<Inner>,
}

impl ChannelPlayer {
    pub async fn create(
        channel_id: &usize,
        client_id: &Option<String>,
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
    ) -> Result<Self, ChannelPlayerError> {
        let inner = Inner::create(
            channel_id,
            client_id,
            path_to_ffmpeg,
            backend_client,
            logger,
            metrics,
        )
        .await?;

        Ok(Self { inner })
    }

    pub fn create_receiver(&self) -> mpsc::Receiver<ChannelPlayerMessage> {
        let (tx, rx) = mpsc::channel(0);

        actix_rt::spawn({
            let senders = self.inner.senders.clone();

            async move {
                senders.lock().unwrap().push(tx);
            }
        });

        rx
    }

    pub fn restart(&self) {
        if let Some(sender) = self.inner.restart_sender.lock().unwrap().take() {
            let _ = sender.send(());
        }
    }
}

struct Inner {
    logger: Logger,
    senders: Arc<Mutex<Vec<mpsc::Sender<ChannelPlayerMessage>>>>,
    current_channel_title: Arc<RwLock<Option<String>>>,
    current_track_title: Arc<RwLock<Option<String>>>,
    restart_sender: Arc<Mutex<Option<oneshot::Sender<()>>>>,
    handle: Arc<Mutex<Option<JoinHandle<()>>>>,
}

impl Drop for Inner {
    fn drop(&mut self) {
        debug!(self.logger, "1");
        if let Some(handle) = self.handle.lock().unwrap().take() {
            debug!(self.logger, "2");
            handle.abort();
            debug!(self.logger, "3");
        }

        debug!(self.logger, "Shared player has been destroyed");
    }
}

impl Inner {
    pub async fn create(
        channel_id: &usize,
        client_id: &Option<String>,
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
    ) -> Result<Arc<Self>, ChannelPlayerError> {
        let senders: Arc<Mutex<Vec<mpsc::Sender<_>>>> = Arc::default();
        let restart_sender: Arc<Mutex<Option<_>>> = Arc::default();
        let current_channel_title: Arc<RwLock<Option<String>>> = Arc::default();
        let current_track_title: Arc<RwLock<Option<String>>> = Arc::default();
        let handle: Arc<Mutex<Option<_>>> = Arc::default();

        let logger = logger.new(o!("channel_id" => *channel_id));

        let mut player_loop_messages = match make_player_loop(
            channel_id,
            client_id,
            path_to_ffmpeg,
            backend_client,
            &logger.new(o!("service" => "player_loop")),
            metrics,
        )
        .await
        {
            Ok(player_loop_events) => player_loop_events,
            Err(error) => {
                return Err(ChannelPlayerError::PlayerLoopError(error));
            }
        };

        let inner = Arc::new(Self {
            logger,
            senders,
            restart_sender,
            current_channel_title,
            current_track_title,
            handle,
        });

        let handle = actix_rt::spawn({
            let inner = Arc::downgrade(&inner);

            async move {
                while let Some(message) = player_loop_messages.next().await {
                    if let Some(inner) = inner.upgrade() {
                        match message {
                            PlayerLoopMessage::ChannelTitle(title) => {
                                inner.update_current_channel_title(title.clone());
                                inner
                                    .send_all(ChannelPlayerMessage::ChannelTitle(title))
                                    .await;
                            }
                            PlayerLoopMessage::TrackTitle(title) => {
                                inner.update_current_track_title(title.clone());
                                inner
                                    .send_all(ChannelPlayerMessage::TrackTitle(title))
                                    .await;
                            }
                            PlayerLoopMessage::TimedBuffer(buffer) => {
                                inner
                                    .send_all(ChannelPlayerMessage::TimedBuffer(buffer))
                                    .await;
                            }
                            PlayerLoopMessage::RestartSender(sender) => {
                                inner.update_restart_sender(sender);
                            }
                        }
                    }
                }
            }
        });

        inner.handle.lock().unwrap().replace(handle);

        Ok(inner)
    }

    fn update_current_channel_title(&self, title: String) {
        let _ = self.current_channel_title.write().unwrap().replace(title);
    }

    fn update_current_track_title(&self, title: String) {
        let _ = self.current_track_title.write().unwrap().replace(title);
    }

    fn update_restart_sender(&self, sender: oneshot::Sender<()>) {
        let _ = self.restart_sender.lock().unwrap().replace(sender);
    }

    async fn send_all(&self, event: ChannelPlayerMessage) {
        let logger = self.logger.clone();
        let txs = self.senders.clone();

        let mut has_disconnected_senders = false;

        for tx in txs.lock().unwrap().iter_mut() {
            if let Err(_) = tx.send(event.clone()).await {
                debug!(logger, "Unable to send event: channel closed");
                has_disconnected_senders = true;
            }
        }

        if has_disconnected_senders {
            debug!(logger, "Performing retain");
            txs.lock().unwrap().retain(|s| !s.is_closed());
        }

        // TODO If no active receivers left, lets start the player shutdown timeout.
    }
}
