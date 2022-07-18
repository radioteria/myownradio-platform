use actix_rt::time::Instant;
use actix_web::web::Bytes;
use std::time::Duration;

#[derive(Clone, Debug)]
pub(crate) struct DecodedBuffer {
    bytes: Bytes,
    dts: Duration,
}

impl DecodedBuffer {
    pub(crate) fn new(bytes: Bytes, dts: Duration) -> Self {
        DecodedBuffer { bytes, dts }
    }

    pub(crate) fn bytes(&self) -> &Bytes {
        &self.bytes
    }

    pub(crate) fn dts(&self) -> &Duration {
        &self.dts
    }
}

#[derive(Clone, Debug)]
pub(crate) struct TimedBuffer(pub(crate) Bytes, pub(crate) Instant);
