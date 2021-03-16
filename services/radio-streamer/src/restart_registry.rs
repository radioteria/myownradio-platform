use futures::channel::oneshot;
use slog::{debug, warn, Logger};
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};
use uuid::Uuid;

pub struct RestartRegistry {
    senders: Arc<Mutex<HashMap<usize, HashMap<Uuid, oneshot::Sender<()>>>>>,
    logger: Logger,
}

impl RestartRegistry {
    pub fn new(logger: Logger) -> Self {
        let senders = Arc::new(Mutex::new(HashMap::default()));

        RestartRegistry { senders, logger }
    }

    pub fn register_restart_sender(&self, channel_id: &usize, sender: oneshot::Sender<()>) -> Uuid {
        let uuid = Uuid::new_v4();

        let mut mtx = self.senders.lock().unwrap();

        let map = match mtx.entry(*channel_id) {
            Entry::Occupied(e) => e.into_mut(),
            Entry::Vacant(e) => e.insert(HashMap::new()),
        };

        debug!(
            self.logger,
            "Register stream restart handler"; "channel_id" => ?channel_id, "uuid" => ?uuid,
        );

        if let Some(sender) = map.insert(uuid.clone(), sender) {
            warn!(self.logger, "Sender with associated Uuid already existed"; "uuid" => ?uuid);
            let _ = sender.send(());
        }

        uuid
    }

    pub fn unregister_restart_sender(&self, channel_id: &usize, uuid: Uuid) {
        if let Entry::Occupied(entry) = self.senders.lock().unwrap().entry(*channel_id) {
            let entry = entry.into_mut();

            debug!(
                self.logger,
                "Unregister stream restart handler"; "channel_id" => ?channel_id, "uuid" => ?uuid,
            );

            if let None = entry.remove(&uuid) {
                warn!(self.logger, "Sender with associated Uuid did not exist"; "uuid" => ?uuid);
            }
        }
    }

    pub fn restart(&self, channel_id: &usize) {
        if let Some(senders_map) = self.senders.lock().unwrap().remove(channel_id) {
            let senders: Vec<_> = senders_map.into_iter().map(|(_, sender)| sender).collect();
            for sender in senders {
                let _ = sender.send(());
            }
        }
    }
}
