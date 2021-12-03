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
    pub async fn create<F>(
        channel_id: &usize,
        client_id: &Option<String>,
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
        on_all_receivers_disconnected: F,
    ) -> Result<Self, ChannelPlayerError>
    where
        F: Fn() -> () + 'static,
    {
        let inner = Inner::create(
            channel_id,
            client_id,
            path_to_ffmpeg,
            backend_client,
            logger,
            metrics,
            on_all_receivers_disconnected,
        )
        .await?;

        Ok(Self { inner })
    }

    pub fn create_receiver(&self) -> mpsc::Receiver<ChannelPlayerMessage> {
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

    pub fn restart(&self) {
        if let Some(sender) = self.inner.restart_sender.lock().unwrap().take() {
            let _ = sender.send(());
        }
    }

    pub fn get_channel_title(&self) -> Option<String> {
        self.inner.current_channel_title.read().unwrap().clone()
    }

    pub fn get_track_title(&self) -> Option<String> {
        self.inner.current_track_title.read().unwrap().clone()
    }
}

struct Inner {
    logger: Logger,
    senders: Arc<Mutex<Vec<mpsc::Sender<ChannelPlayerMessage>>>>,
    current_channel_title: RwLock<Option<String>>,
    current_track_title: RwLock<Option<String>>,
    restart_sender: Mutex<Option<oneshot::Sender<()>>>,
    handle: Mutex<Option<JoinHandle<()>>>,
    on_all_receivers_disconnected: Box<dyn Fn() -> ()>,
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
        channel_id: &usize,
        client_id: &Option<String>,
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
        on_all_receivers_disconnected: F,
    ) -> Result<Arc<Self>, ChannelPlayerError>
    where
        F: Fn() -> () + 'static,
    {
        let senders: Arc<Mutex<Vec<mpsc::Sender<_>>>> = Arc::default();
        let restart_sender: Mutex<Option<_>> = Mutex::default();
        let current_channel_title: RwLock<Option<String>> = RwLock::default();
        let current_track_title: RwLock<Option<String>> = RwLock::default();
        let handle: Mutex<Option<_>> = Mutex::default();

        let logger = logger.new(o!("channel_id" => *channel_id));

        let mut player_loop_messages = {
            let player_loop_logger = logger.new(o!("service" => "player_loop"));

            match make_player_loop(
                channel_id,
                client_id,
                path_to_ffmpeg,
                backend_client,
                &player_loop_logger,
                metrics,
            )
            .await
            {
                Ok(player_loop_events) => player_loop_events,
                Err(error) => {
                    return Err(ChannelPlayerError::PlayerLoopError(error));
                }
            }
        };

        let inner = Arc::new(Self {
            logger,
            senders,
            restart_sender,
            current_channel_title,
            current_track_title,
            handle,
            on_all_receivers_disconnected: Box::new(on_all_receivers_disconnected),
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
