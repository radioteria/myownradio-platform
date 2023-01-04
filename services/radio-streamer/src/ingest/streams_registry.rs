use crate::ingest::stream::Stream;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

pub(crate) struct StreamRegistry {
    streams_map: Mutex<HashMap<usize, Arc<Stream>>>,
}

impl StreamRegistry {
    pub(crate) fn get_single_stream(&self, channel_id: &usize) -> Option<Arc<Stream>> {
        self.streams_map
            .lock()
            .unwrap()
            .get(channel_id)
            .map(Clone::clone)
    }
}
