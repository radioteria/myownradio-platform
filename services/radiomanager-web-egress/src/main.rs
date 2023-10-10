use crate::config::Config;
use crate::stream::{create_stream, StreamConfig, StreamOutput};
use std::time::Duration;

pub(crate) mod config;
pub(crate) mod gstreamer_utils;
pub(crate) mod pipeline;
pub(crate) mod stream;

pub(crate) fn main() {
    tracing_subscriber::fmt::init();

    let config = Config::from_env();

    gstreamer::init().expect("Unable to initialize GStreamer!");

    create_stream(
        config.webpage_url.clone(),
        &StreamConfig {
            output: StreamOutput::RTMP {
                url: config.rtmp_url.clone(),
                stream_key: config.rtmp_stream_key.clone(),
            },
        },
    )
    .expect("Unable to create stream");

    loop {
        std::thread::sleep(Duration::from_secs(1));
    }
}
