use futures::channel::oneshot::Sender;
use slog::{debug, warn, Logger};
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use uuid::Uuid;

pub struct RestartRegistry {
    senders: HashMap<usize, HashMap<Uuid, Sender<()>>>,
    logger: Logger,
}

impl RestartRegistry {
    pub fn new(logger: Logger) -> Self {
        let senders = HashMap::default();

        RestartRegistry { senders, logger }
    }

    pub fn register_restart_sender(&mut self, channel_id: &usize, sender: Sender<()>) -> Uuid {
        let uuid = Uuid::new_v4();

        let map = match self.senders.entry(channel_id.clone()) {
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

    pub fn unregister_restart_sender(&mut self, channel_id: &usize, uuid: Uuid) {
        if let Entry::Occupied(entry) = self.senders.entry(channel_id.clone()) {
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

    pub fn restart(&mut self, channel_id: &usize) {
        match self.senders.remove(channel_id) {
            Some(map) => {
                map.into_iter().for_each(|(_, sender)| {
                    let _ = sender.send(());
                });
            }
            None => {
                // Nothing
            }
        }
    }
}
