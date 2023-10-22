use serde::{Deserialize, Serialize};
use std::ops::Deref;

#[derive(Clone, Debug, Deserialize, Serialize)]
pub(crate) struct UserId(i32);

impl Deref for UserId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl From<i32> for UserId {
    fn from(id: i32) -> Self {
        UserId(id)
    }
}

#[derive(Deserialize)]
pub(crate) struct RtmpSettings {
    pub(crate) rtmp_url: String,
    pub(crate) stream_key: String,
}

#[derive(Deserialize)]
pub(crate) struct VideoSettings {
    pub(crate) width: u32,
    pub(crate) height: u32,
    pub(crate) framerate: u32,
    pub(crate) bitrate: u32,
}

#[derive(Deserialize)]
pub(crate) struct AudioSettings {
    pub(crate) bitrate: u32,
    pub(crate) channels: u32,
}
