use crate::backend_client::BackendClient;
use crate::ingest::stream::{StopReason, Stream, StreamCreateError};
use crate::metrics::Metrics;
use slog::Logger;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

pub(crate) struct StreamsRegistry {
    path_to_ffmpeg: String,
    backend_client: BackendClient,
    logger: Logger,
    metrics: Metrics,
    streams_map: Mutex<HashMap<usize, Arc<Stream>>>,
}

impl StreamsRegistry {
    pub(crate) fn new(
        path_to_ffmpeg: &str,
        backend_client: &BackendClient,
        logger: &Logger,
        metrics: &Metrics,
    ) -> Self {
        let streams_map = Mutex::new(HashMap::new());

        Self {
            path_to_ffmpeg: path_to_ffmpeg.to_string(),
            backend_client: backend_client.clone(),
            logger: logger.clone(),
            metrics: metrics.clone(),
            streams_map,
        }
    }

    pub(crate) fn get_single_stream(&self, channel_id: &usize) -> Option<Arc<Stream>> {
        self.streams_map
            .lock()
            .unwrap()
            .get(channel_id)
            .map(Clone::clone)
    }

    fn register_stream(&self, channel_id: &usize, stream: Arc<Stream>) {
        let _ = self.streams_map.lock().unwrap().insert(*channel_id, stream);
    }

    pub(crate) fn stop_and_unregister_stream(&self, channel_id: &usize, reason: StopReason) {
        if let Some(stream) = self.streams_map.lock().unwrap().remove(channel_id) {
            stream.stop(reason);
        }
    }
}

#[async_trait::async_trait(?Send)]
pub(crate) trait StreamsRegistryExt {
    async fn get_or_create_stream(
        &self,
        channel_id: &usize,
    ) -> Result<Arc<Stream>, StreamCreateError>;
}

#[async_trait::async_trait(?Send)]
impl StreamsRegistryExt for Arc<StreamsRegistry> {
    async fn get_or_create_stream(
        &self,
        channel_id: &usize,
    ) -> Result<Arc<Stream>, StreamCreateError> {
        if let Some(stream) = self.streams_map.lock().unwrap().get(channel_id) {
            return Ok(stream.clone());
        }

        let stream = Arc::new(
            Stream::create(
                channel_id,
                &self.path_to_ffmpeg,
                &self.backend_client,
                &self.logger,
                &self.metrics,
                Arc::clone(self),
            )
            .await?,
        );

        self.streams_map
            .lock()
            .unwrap()
            .insert(*channel_id, Arc::clone(&stream));

        Ok(stream)
    }
}
