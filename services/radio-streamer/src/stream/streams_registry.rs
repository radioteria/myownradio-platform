use super::stream::{StopReason, Stream, StreamCreateError};
use crate::backend_client::BackendClient;
use crate::metrics::Metrics;
use slog::Logger;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

pub(crate) struct StreamsRegistry {
    backend_client: BackendClient,
    logger: Logger,
    metrics: Metrics,
    streams_map: Mutex<HashMap<usize, Arc<Stream>>>,
}

impl StreamsRegistry {
    pub(crate) fn new(backend_client: &BackendClient, logger: &Logger, metrics: &Metrics) -> Self {
        let streams_map = Mutex::new(HashMap::new());

        Self {
            backend_client: backend_client.clone(),
            logger: logger.clone(),
            metrics: metrics.clone(),
            streams_map,
        }
    }

    pub(crate) fn get_stream(&self, channel_id: &usize) -> Option<Arc<Stream>> {
        self.streams_map
            .lock()
            .unwrap()
            .get(channel_id)
            .map(Arc::clone)
    }

    fn register_stream(&self, channel_id: &usize, stream: Arc<Stream>) {
        let _ = self.streams_map.lock().unwrap().insert(*channel_id, stream);
    }

    pub(crate) fn unregister_stream(&self, channel_id: &usize, _reason: StopReason) {
        if let Some(_stream) = self.streams_map.lock().unwrap().remove(channel_id) {
            // @todo Do something with removed stream
        }
    }

    pub(crate) fn restart_stream(&self, channel_id: &usize) {
        if let Some(stream) = self.streams_map.lock().unwrap().remove(channel_id) {
            stream.restart();
        }
    }

    pub fn get_channel_ids(&self) -> Vec<usize> {
        self.streams_map.lock().unwrap().keys().cloned().collect()
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
                &self.backend_client,
                &self.logger,
                &self.metrics,
                Arc::clone(self),
            )
            .await?,
        );

        self.register_stream(channel_id, Arc::clone(&stream));

        Ok(stream)
    }
}
