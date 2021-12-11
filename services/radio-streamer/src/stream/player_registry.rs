use crate::stream::channel_player::{ChannelPlayer, ChannelPlayerError};
use crate::{BackendClient, Metrics};
use slog::{debug, Logger};
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

#[derive(Debug)]
pub(crate) enum PlayerRegistryError {
    ChannelPlayerError(ChannelPlayerError),
}

#[derive(Clone)]
pub(crate) struct PlayerRegistry {
    path_to_ffmpeg: String,
    backend_client: Arc<BackendClient>,
    logger: Logger,
    metrics: Arc<Metrics>,
    players_map: Arc<Mutex<HashMap<PlayerEntry, ChannelPlayer>>>,
}

#[derive(Debug, Hash, Eq, PartialEq, Clone)]
struct PlayerEntry(usize, Option<String>);

impl PlayerRegistry {
    pub fn new(
        path_to_ffmpeg: String,
        backend_client: Arc<BackendClient>,
        logger: Logger,
        metrics: Arc<Metrics>,
    ) -> Self {
        let players_map = Arc::default();

        Self {
            path_to_ffmpeg,
            backend_client,
            logger,
            metrics,
            players_map,
        }
    }

    pub fn restart_by_channel_id(&self, channel_id: &usize) {
        for (PlayerEntry(id, _), player) in self.players_map.lock().unwrap().iter() {
            if id == channel_id {
                player.restart();
            }
        }
    }

    pub fn get_channel_ids(&self) -> Vec<usize> {
        self.players_map
            .lock()
            .unwrap()
            .keys()
            .map(|PlayerEntry(channel_id, _)| channel_id)
            .cloned()
            .collect()
    }

    pub async fn get_player(
        &self,
        channel_id: &usize,
        client_id: &Option<String>,
    ) -> Result<ChannelPlayer, PlayerRegistryError> {
        let key = PlayerEntry(channel_id.clone(), client_id.clone());

        let player = match self.players_map.lock().unwrap().entry(key.clone()) {
            Entry::Occupied(entry) => {
                debug!(self.logger, "Reusing existing channel player"; "channel_id" => ?channel_id);

                entry.get().clone()
            }
            Entry::Vacant(entry) => {
                debug!(self.logger, "Creating new channel player"; "channel_id" => ?channel_id);

                let player = match ChannelPlayer::create(
                    channel_id,
                    client_id,
                    &self.path_to_ffmpeg,
                    &self.backend_client,
                    &self.logger,
                    &self.metrics,
                    {
                        let players_map = self.players_map.clone();

                        move || {
                            players_map.lock().unwrap().remove(&key);
                        }
                    },
                )
                .await
                {
                    Ok(player) => player,
                    Err(error) => return Err(PlayerRegistryError::ChannelPlayerError(error)),
                };

                entry.insert(player.clone());

                player
            }
        };

        Ok(player)
    }
}
