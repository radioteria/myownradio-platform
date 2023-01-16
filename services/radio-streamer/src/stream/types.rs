use actix_web::web::Bytes;
use std::time::Duration;

#[derive(Clone, Debug)]
pub(crate) struct Buffer {
    bytes: Bytes,
    pts_hint: Duration,
    dts_hint: Duration,
}

impl Buffer {
    pub(crate) fn new(bytes: Bytes, pts_hint: Duration, dts_hint: Duration) -> Self {
        Buffer {
            bytes,
            pts_hint,
            dts_hint,
        }
    }

    pub(crate) fn bytes(&self) -> &Bytes {
        &self.bytes
    }

    pub(crate) fn pts_hint(&self) -> &Duration {
        &self.pts_hint
    }

    pub(crate) fn dts_hint(&self) -> &Duration {
        &self.dts_hint
    }

    pub(crate) fn is_empty(&self) -> bool {
        self.bytes.is_empty()
    }

    pub(crate) fn into_bytes(self) -> Bytes {
        self.bytes
    }
}
