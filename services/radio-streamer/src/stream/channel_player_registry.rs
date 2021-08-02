use crate::codec::AudioCodecService;
use crate::metrics::Metrics;
use crate::mor_backend_client::MorBackendClient;
use crate::stream::channel_player_factory::ChannelPlayer;
use slog::Logger;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

#[derive(Hash, Eq, PartialEq)]
pub struct ChannelKey(usize, Option<String>);

pub struct ChannelPlayerRegistry {
    channels: Mutex<HashMap<ChannelKey, Arc<ChannelPlayer>>>,
}

impl ChannelPlayerRegistry {
    pub fn create() -> Self {
        let channels = Mutex::<HashMap<ChannelKey, Arc<ChannelPlayer>>>::default();

        ChannelPlayerRegistry { channels }
    }

    pub fn register_channel_player(
        &self,
        channel_key: ChannelKey,
        channel_player: Arc<ChannelPlayer>,
    ) {
        let _ = self
            .channels
            .lock()
            .unwrap()
            .insert(channel_key, channel_player.clone());
    }

    pub fn unregister_channel_player(&self, channel_key: &ChannelKey) {
        let _ = self.channels.lock().unwrap().remove(channel_key);
    }
}
