use futures::channel::mpsc;
use futures::StreamExt;
use slog::Logger;
use std::collections::HashSet;
use std::io::Read;
use std::sync::{Arc, RwLock};
use std::time::Duration;

pub(crate) const SHARED_PLAYER_EMPTY_TIMEOUT: Duration = Duration::from_secs(30);

#[derive(Debug)]
pub(crate) enum SharedPlayerError {}

#[derive(Clone, Debug)]
pub(crate) enum SharedPlayerEvent {}

pub(crate) struct SharedPlayer {
    txs: Arc<RwLock<HashSet<mpsc::Sender<SharedPlayerEvent>>>>,
    current_channel_title: Arc<RwLock<Option<String>>>,
    current_track_title: Arc<RwLock<Option<String>>>,
}

impl SharedPlayer {
    pub fn create(logger: &Logger) -> Result<Self, SharedPlayerError> {
        let (tx, mut rx) = mpsc::channel::<SharedPlayerEvent>(0);
        let txs: Arc<RwLock<HashSet<mpsc::Sender<_>>>> = Arc::default();
        let current_channel_title = Arc::default();
        let current_track_title = Arc::default();

        actix_rt::spawn({
            let logger = logger.clone();
            let txs = txs.clone();

            async move {
                while let Some(event) = rx.next().await {
                    let mut disconnected_txs = HashSet::new();

                    for mut tx in txs.read().unwrap().iter() {
                        if let Err(error) = tx.try_send(event.clone()) {
                            if error.is_full() {
                                // Buffer is full: skip sending message
                            } else if error.is_disconnected() {
                                // Disconnected: remove client
                                disconnected_txs.insert(tx)
                            }
                        }
                    }

                    let mut txs = txs.write().unwrap();

                    for tx in disconnected_txs.into_iter() {
                        txs.remove(tx);
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
}
