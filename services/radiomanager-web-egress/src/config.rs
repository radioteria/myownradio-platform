use serde::Deserialize;
use std::env;

fn from_str<'de, D>(deserializer: D) -> Result<u32, D::Error>
where
    D: serde::Deserializer<'de>,
{
    let s: String = Deserialize::deserialize(deserializer)?;
    s.parse::<u32>().map_err(serde::de::Error::custom)
}

#[derive(Deserialize)]
pub(crate) struct VideoSettings {
    #[serde(rename = "video_width", deserialize_with = "from_str")]
    pub(crate) width: u32,
    #[serde(rename = "video_height", deserialize_with = "from_str")]
    pub(crate) height: u32,
    #[serde(rename = "video_bitrate", deserialize_with = "from_str")]
    pub(crate) bitrate: u32,
    #[serde(rename = "video_framerate", deserialize_with = "from_str")]
    pub(crate) framerate: u32,
}

#[derive(Deserialize)]
pub(crate) struct AudioSettings {
    #[serde(rename = "audio_bitrate", deserialize_with = "from_str")]
    pub(crate) bitrate: u32,
}

#[derive(Deserialize)]
pub(crate) struct Config {
    pub(crate) webpage_url: String,
    pub(crate) rtmp_url: String,
    pub(crate) rtmp_stream_key: String,
    #[serde(flatten)]
    pub(crate) audio: AudioSettings,
    #[serde(flatten)]
    pub(crate) video: VideoSettings,
}

impl Config {
    pub(crate) fn from_env() -> Self {
        envy::from_env().expect("Unable to parse environment variables")
    }
}
