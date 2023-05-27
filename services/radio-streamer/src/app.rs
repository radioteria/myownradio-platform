use crate::audio_stream::AudioStream;
use crate::backend_client::BackendClient;
use crate::types::ChannelId;
use myownradio_ffmpeg_utils::OutputFormat;
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex, Weak};

#[derive(Clone)]
pub(crate) struct StaticState {
    backend_client: Arc<BackendClient>,
}

#[derive(Clone)]
pub(crate) struct ChannelEntry(ChannelId, OutputFormat);

#[derive(Clone)]
pub(crate) struct DynamicState {
    channels: Arc<Mutex<HashMap<ChannelEntry, Weak<AudioStream>>>>,
}

#[derive(Clone)]
pub(crate) struct App {
    static_state: Arc<StaticState>,
    dynamic_state: Arc<DynamicState>,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateChannelStreamError {}

impl App {
    pub(crate) fn get_or_create_channel_stream(
        &self,
        channel_id: u64,
        output_format: OutputFormat,
    ) -> Result<Arc<AudioStream>, CreateChannelStreamError> {
        let channel_entry = ChannelEntry(channel_id.into(), output_format);
        let mut guard = self.dynamic_state.channels.lock().unwrap();

        let audio_stream = match guard.entry(channel_entry) {
            Entry::Occupied(entry) => {
                if let Some(audio_stream) = entry.get().upgrade() {
                    return Ok(audio_stream);
                }
                self.cleanup_stale_streams();
            }
            Entry::Vacant(entry) => {}
        };

        todo!();
    }

    pub(crate) fn restart_channel(&self, channel_id: &u64) {
        let guard = self.dynamic_state.channels.lock().unwrap();

        for stream in guard
            .iter()
            .filter(|(key, _)| key.0 == channel_id)
            .filter_map(|(_, weak_stream)| weak_stream.upgrade())
        {
            stream.restart();
        }
    }

    fn cleanup_stale_streams(&self) {
        let mut guard = self.dynamic_state.channels.lock().unwrap();

        guard.retain(|key, weak_stream| weak_stream.upgrade().is_some());
    }
}
