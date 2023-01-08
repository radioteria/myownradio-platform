use actix_web::web::Bytes;
use std::sync::Arc;
use std::time::Duration;

#[derive(Debug)]
pub(crate) struct Format {
    pub(crate) codec: String,
    pub(crate) container: String,
    pub(crate) channels: usize,
    pub(crate) bitrate: usize,
    pub(crate) sample_rate: usize,
}

#[derive(Clone, Debug)]
pub(crate) struct Buffer {
    format: Arc<Format>,
    bytes: Bytes,
    dts: Duration,
}

impl Buffer {
    pub(crate) fn new(bytes: Bytes, dts: Duration, format: &Arc<Format>) -> Self {
        let format = Arc::clone(format);

        Buffer { bytes, dts, format }
    }

    pub(crate) fn bytes(&self) -> &Bytes {
        &self.bytes
    }

    pub(crate) fn dts(&self) -> &Duration {
        &self.dts
    }

    pub(crate) fn format(&self) -> &Arc<Format> {
        &self.format
    }

    pub(crate) fn is_empty(&self) -> bool {
        self.bytes.is_empty()
    }

    pub(crate) fn into_bytes(self) -> Bytes {
        self.bytes
    }
}
