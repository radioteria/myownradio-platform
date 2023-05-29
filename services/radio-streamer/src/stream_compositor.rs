use crate::audio_stream::{AudioStream, CreateAudioStreamError};
use crate::backend_client::BackendClient;
use crate::types::ChannelId;
use futures::lock::Mutex;
use myownradio_ffmpeg_utils::OutputFormat;
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Weak};
use tracing::debug;

#[derive(Clone)]
pub(crate) struct StaticState {
    backend_client: Arc<BackendClient>,
}

#[derive(Clone, Eq, Hash, PartialEq)]
pub(crate) struct ChannelEntry(ChannelId, OutputFormat);

#[derive(Clone)]
pub(crate) struct DynamicState {
    channels: Arc<Mutex<HashMap<ChannelEntry, Weak<AudioStream>>>>,
}

#[derive(Clone)]
pub(crate) struct StreamCompositor {
    static_state: Arc<StaticState>,
    dynamic_state: Arc<DynamicState>,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum GetOrCreateAudioStreamError {
    #[error(transparent)]
    CreateAudioStreamError(#[from] CreateAudioStreamError),
}

impl StreamCompositor {
    pub(crate) fn create(backend_client: Arc<BackendClient>) -> Self {
        let static_state = Arc::new(StaticState { backend_client });

        let dynamic_state = Arc::new(DynamicState {
            channels: Arc::new(Mutex::new(HashMap::new())),
        });

        Self {
            static_state,
            dynamic_state,
        }
    }

    pub(crate) async fn get_or_create_audio_stream(
        &self,
        channel_id: &ChannelId,
        output_format: &OutputFormat,
    ) -> Result<Arc<AudioStream>, GetOrCreateAudioStreamError> {
        debug!(?channel_id, "Requested audio stream");

        let channel_entry = ChannelEntry(channel_id.clone(), output_format.clone());
        let mut guard = self.dynamic_state.channels.lock().await;

        let audio_stream = loop {
            match guard.entry(channel_entry.clone()) {
                Entry::Occupied(entry) => match entry.get().upgrade() {
                    Some(audio_stream) => {
                        debug!(?channel_id, "Reusing existing audio stream");

                        break audio_stream;
                    }
                    None => {
                        debug!(?channel_id, "Removing stale audio stream");

                        entry.remove()
                    }
                },
                Entry::Vacant(entry) => {
                    debug!(?channel_id, "Creating audio stream");
                    let audio_stream = Arc::new(
                        AudioStream::create(
                            channel_id,
                            output_format,
                            &self.static_state.backend_client,
                        )
                        .await?,
                    );
                    entry.insert(Arc::downgrade(&audio_stream));

                    break audio_stream;
                }
            };
        };

        Ok(audio_stream)
    }

    pub(crate) async fn restart_channel_streams(&self, channel_id: &ChannelId) {
        let guard = self.dynamic_state.channels.lock().await;

        for stream in guard
            .iter()
            .filter(|(key, _)| &key.0 == channel_id)
            .filter_map(|(_, weak_stream)| weak_stream.upgrade())
        {
            stream.restart().await;
        }

        drop(guard);
    }
}
