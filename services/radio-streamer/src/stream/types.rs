use actix_web::web::Bytes;
use std::time::Duration;

#[derive(Clone, Debug)]
pub(crate) struct DecodedBuffer {
    bytes: Bytes,
    dts: Duration,
    pts: Duration,
}

impl DecodedBuffer {
    pub(crate) fn new(bytes: Bytes, dts: Duration, pts: Duration) -> Self {
        DecodedBuffer { bytes, dts, pts }
    }

    pub(crate) fn bytes(&self) -> &Bytes {
        &self.bytes
    }

    pub(crate) fn dts(&self) -> &Duration {
        &self.dts
    }

    pub(crate) fn pts(&self) -> &Duration {
        &self.pts
    }
}
