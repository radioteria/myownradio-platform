use crate::channel::channel_player_factory::ChannelPlayer;
use crate::metrics::Metrics;
use crate::mor_backend_client::MorBackendClient;
use crate::transcoder::TranscoderService;
use slog::Logger;
use std::collections::HashMap;
use std::sync::{Arc, Mutex, Weak};

#[derive(Hash, Eq, PartialEq)]
pub struct ChannelKey(usize, Option<String>);

pub struct ChannelPlayerRegistry {
    channels: Mutex<HashMap<ChannelKey, Weak<ChannelPlayer>>>,
}

impl ChannelPlayerRegistry {
    pub fn create() -> Self {
        let channels = Default::default();

        ChannelPlayerRegistry { channels }
    }

    pub fn register_channel_player(
        &self,
        channel_key: ChannelKey,
        channel_player: Arc<ChannelPlayer>,
    ) {
        let weak = Arc::downgrade(&channel_player);

        let _ = self.channels.lock().unwrap().insert(channel_key, weak);
    }

    pub fn unregister_channel_player(&self, channel_key: &ChannelKey) {
        let _ = self.channels.lock().unwrap().remove(channel_key);
    }

    pub fn get_channel_player(&self, channel_key: &ChannelKey) -> Option<Arc<ChannelPlayer>> {
        self.channels
            .lock()
            .unwrap()
            .get(channel_key)
            .and_then(|weak| weak.upgrade())
    }
}
