use actix_web::web::Bytes;
use std::ops::Deref;
use std::time::Duration;

#[derive(Clone, Debug)]
pub(crate) struct Buffer {
    bytes: Bytes,
    pts_hint: Duration,
}

impl Buffer {
    pub(crate) fn new(bytes: Bytes, pts_hint: Duration) -> Self {
        Buffer { bytes, pts_hint }
    }

    pub(crate) fn bytes(&self) -> &Bytes {
        &self.bytes
    }

    pub(crate) fn pts_hint(&self) -> &Duration {
        &self.pts_hint
    }
}

impl Deref for Buffer {
    type Target = Bytes;

    fn deref(&self) -> &Self::Target {
        &self.bytes
    }
}

#[derive(Clone, Debug)]
pub(crate) struct TrackTitle {
    title: String,
    pts_hint: Duration,
}

impl TrackTitle {
    pub(crate) fn new(title: String, pts_hint: Duration) -> Self {
        TrackTitle { title, pts_hint }
    }

    pub(crate) fn title(&self) -> &str {
        &self.title
    }

    pub(crate) fn pts_hint(&self) -> &Duration {
        &self.pts_hint
    }
}

impl Deref for TrackTitle {
    type Target = String;

    fn deref(&self) -> &Self::Target {
        &self.title
    }
}
