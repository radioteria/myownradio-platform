use crate::backend_client::BackendClient;
use crate::metrics::Metrics;
use crate::stream::player_loop::{make_player_loop, PlayerLoopMessage};
use crate::stream::types::TimedBuffer;
use actix_rt::task::JoinHandle;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use slog::{debug, o, warn, Logger};
use std::sync::{Arc, Mutex, RwLock};

#[derive(Debug)]
pub(crate) enum ChannelPlayerError {}

#[derive(Clone, Debug)]
pub(crate) enum ChannelPlayerMessage {
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
        F: 'static + Fn() -> () + Send + Sync,
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

        let mut senders = self.inner.senders.lock().unwrap();

        senders.push(tx);

        rx
    }

    pub fn restart(&self) {
        if let Some(sender) = self.inner.restart_sender.lock().unwrap().take() {
            let _ = sender.send(());
        }
    }

    pub fn get_track_title(&self) -> Option<String> {
        self.inner.current_track_title.read().unwrap().clone()
    }
}

struct Inner {
    logger: Logger,
    senders: Arc<Mutex<Vec<mpsc::Sender<ChannelPlayerMessage>>>>,
    current_track_title: RwLock<Option<String>>,
    restart_sender: Mutex<Option<oneshot::Sender<()>>>,
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
        channel_id: &usize,
        client_id: &Option<String>,
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
        on_all_receivers_disconnected: F,
    ) -> Result<Arc<Self>, ChannelPlayerError>
    where
        F: 'static + Fn() -> () + Send + Sync,
    {
        let senders: Arc<Mutex<Vec<mpsc::Sender<_>>>> = Arc::default();
        let restart_sender: Mutex<Option<_>> = Mutex::default();
        let current_track_title: RwLock<Option<String>> = RwLock::default();
        let handle: Mutex<Option<_>> = Mutex::default();

        let logger = logger.new(o!("channel_id" => *channel_id));

        let mut player_loop_messages = {
            make_player_loop(
                channel_id,
                client_id,
                path_to_ffmpeg,
                backend_client,
                &logger,
                metrics,
            )
        };

        let inner = Arc::new(Self {
            logger,
            senders,
            restart_sender,
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

    fn update_current_track_title(&self, title: String) {
        let _ = self.current_track_title.write().unwrap().replace(title);
    }

    fn update_restart_sender(&self, sender: oneshot::Sender<()>) {
        let _ = self.restart_sender.lock().unwrap().replace(sender);
    }

    async fn send_all(&self, message: ChannelPlayerMessage) {
        let logger = self.logger.clone();

        let mut senders = self.senders.lock().unwrap();

        if senders.len() == 0 {
            warn!(logger, "Sending message to nobody"; "message" => ?message);
        }

        let mut has_disconnected_senders = false;

        for sender in senders.iter_mut() {
            if let Err(_) = sender.send(message.clone()).await {
                debug!(logger, "Unable to send message: channel closed");
                has_disconnected_senders = true;
            }
        }

        if has_disconnected_senders {
            debug!(logger, "Performing retain");

            senders.retain(|sender| !sender.is_closed());

            if senders.len() == 0 {
                (self.on_all_receivers_disconnected)();
            }
        }
    }
}
