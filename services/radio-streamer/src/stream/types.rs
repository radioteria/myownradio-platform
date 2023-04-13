use crate::stream::util::channels::TimedMessage;
use actix_web::web::Bytes;
use std::ops::Deref;
use std::sync::Arc;
use std::time::Duration;

#[derive(Clone, Debug, PartialEq)]
pub(crate) struct SharedFrame {
    data: Arc<Vec<u8>>,
    duration: Duration,
    pts: Duration,
}

impl SharedFrame {
    pub(crate) fn new(pts: Duration, duration: Duration, data: Vec<u8>) -> Self {
        let data = Arc::new(data);

        Self {
            pts,
            duration,
            data,
        }
    }

    pub(crate) fn data(&self) -> &Arc<Vec<u8>> {
        &self.data
    }

    pub(crate) fn duration(&self) -> &Duration {
        &self.duration
    }

    pub(crate) fn pts(&self) -> &Duration {
        &self.pts
    }

    pub(crate) fn is_empty(&self) -> bool {
        self.data.is_empty()
    }
}

impl TimedMessage for &SharedFrame {
    fn message_pts(&self) -> Duration {
        self.pts.clone()
    }
}

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

    pub(crate) fn is_empty(&self) -> bool {
        self.bytes.is_empty()
    }

    pub(crate) fn into_bytes(self) -> Bytes {
        self.bytes
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
