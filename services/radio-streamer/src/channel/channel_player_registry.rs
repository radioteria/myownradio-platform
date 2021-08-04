use crate::channel::channel_player_factory::ChannelPlayer;
use std::collections::HashMap;
use std::sync::{Arc, Mutex, Weak};

#[derive(Hash, Eq, PartialEq, Clone, Debug)]
pub struct ChannelKey(pub usize, pub Option<String>);

pub struct ChannelPlayerRegistry {
    channels: Mutex<HashMap<ChannelKey, Weak<ChannelPlayer>>>,
}

impl ChannelPlayerRegistry {
    pub fn new() -> Self {
        let channels = Mutex::default();

        ChannelPlayerRegistry { channels }
    }

    pub fn register_channel_player(
        &self,
        channel_key: ChannelKey,
        channel_player: Weak<ChannelPlayer>,
    ) {
        let _ = self
            .channels
            .lock()
            .unwrap()
            .insert(channel_key, channel_player);
    }

    pub fn get_channel_player(&self, channel_key: &ChannelKey) -> Option<Arc<ChannelPlayer>> {
        self.channels
            .lock()
            .unwrap()
            .get(channel_key)
            .and_then(|weak| weak.upgrade())
    }

    pub fn get_channel_players_by_id(&self, channel_id: &usize) -> Vec<Arc<ChannelPlayer>> {
        self.channels
            .lock()
            .unwrap()
            .iter()
            .filter(|(ChannelKey(key_channel_id, _), _)| *key_channel_id == *channel_id)
            .flat_map(|(_, weak)| weak.upgrade())
            .collect()
    }

    pub fn get_all_channel_players(&self) -> Vec<usize> {
        self.channels
            .lock()
            .unwrap()
            .iter()
            .filter(|(_, weak)| weak.upgrade().is_some())
            .map(|(ChannelKey(key_channel_id, _), _)| *key_channel_id)
            .collect()
    }
}
