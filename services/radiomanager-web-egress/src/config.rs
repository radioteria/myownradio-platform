use serde::Deserialize;

#[derive(Deserialize)]
pub(crate) struct Config {
    webpage_url: String,
    rtmp_url: String,
    rtmp_stream_key: String,
}

impl Config {
    pub(crate) fn from_env() -> Self {
        envy::from_env().expect("Unable to parse environment variables")
    }
}
