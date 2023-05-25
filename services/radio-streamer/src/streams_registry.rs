use crate::audio_stream::{AudioStream, AudioStreamMessage, CreateAudioStreamError};
use crate::backend_client::BackendClient;
use myownradio_channel_utils::{Channel, ChannelClosed};
use myownradio_ffmpeg_utils::OutputFormat;
use std::collections::{hash_map::Entry, HashMap};
use std::sync::{mpsc, Arc, Mutex};

#[derive(Eq, Hash, PartialEq)]
pub(crate) struct ChannelEntry(u32, OutputFormat);

#[derive(Debug, thiserror::Error)]
pub(crate) enum GetOrCreateChannelError {
    #[error("CreateAudioStreamError: {0:?}")]
    CreateAudioStreamError(#[from] CreateAudioStreamError),
}

#[derive(Clone)]
pub(crate) struct StreamsRegistry {
    backend_client: BackendClient,
    audio_streams: Arc<Mutex<HashMap<ChannelEntry, AudioStream>>>,
}

impl StreamsRegistry {
    pub(crate) fn create(backend_client: BackendClient) -> Self {
        let audio_streams = Arc::new(Mutex::new(HashMap::new()));

        Self {
            backend_client,
            audio_streams,
        }
    }

    pub(crate) async fn get_or_create_channel(
        &self,
        channel_id: &u32,
        format: &OutputFormat,
    ) -> Result<AudioStream, GetOrCreateChannelError> {
        let channel_entry = ChannelEntry(*channel_id, format.clone());

        let audio_stream = match self.audio_streams.lock().unwrap().entry(channel_entry) {
            Entry::Occupied(entry) => entry.get().clone(),
            Entry::Vacant(entry) => entry
                .insert(
                    AudioStream::create(channel_id, &format, &self.backend_client, &self).await?,
                )
                .clone(),
        };

        Ok(audio_stream)
    }

    pub(crate) fn unregister(&self, channel_id: u32, format: OutputFormat) {
        let channel_entry = ChannelEntry(channel_id, format);
        self.audio_streams.lock().unwrap().remove(&channel_entry);
    }
}
