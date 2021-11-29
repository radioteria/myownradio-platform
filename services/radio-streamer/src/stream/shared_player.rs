use core::ptr;
use futures::channel::mpsc;
use futures::{SinkExt, StreamExt};
use slog::Logger;
use std::collections::HashSet;
use std::io::Read;
use std::sync::{Arc, Mutex, RwLock};
use std::time::Duration;

pub(crate) const SHARED_PLAYER_EMPTY_TIMEOUT: Duration = Duration::from_secs(30);

#[derive(Debug)]
pub(crate) enum SharedPlayerError {}

#[derive(Clone, Debug)]
pub(crate) enum SharedPlayerEvent {}

pub(crate) struct SharedPlayer {
    txs: Arc<Mutex<Vec<mpsc::Sender<SharedPlayerEvent>>>>,
    current_channel_title: Arc<RwLock<Option<String>>>,
    current_track_title: Arc<RwLock<Option<String>>>,
}

impl SharedPlayer {
    pub fn create(logger: &Logger) -> Result<Self, SharedPlayerError> {
        let (tx, mut rx) = mpsc::channel::<SharedPlayerEvent>(0);
        let txs: Arc<Mutex<Vec<mpsc::Sender<_>>>> = Arc::default();
        let current_channel_title = Arc::default();
        let current_track_title = Arc::default();

        actix_rt::spawn({
            let logger = logger.clone();
            let txs = txs.clone();

            async move {
                while let Some(event) = rx.next().await {
                    let mut has_disconnected_senders = false;

                    for tx in txs.lock().expect("Unable to acquire lock").iter_mut() {
                        if let Err(_) = tx.send(event.clone()).await {
                            debug!(logger, "Unable to send event: channel closed");
                            has_disconnected_senders = true;
                        }
                    }

                    if has_disconnected_senders {
                        txs.lock()
                            .expect("Unable to acquire lock")
                            .retain(|s| !s.is_closed());
                    }
                }
            }
        });

        Ok(Self {
            txs,
            current_channel_title,
            current_track_title,
        })
    }

    pub fn connect(&self) -> mpsc::Receiver<SharedPlayerEvent> {
        let (tx, rx) = mpsc::channel(0);

        self.txs.lock().unwrap().push(tx);

        rx
    }
}
