use crate::stream::{create_stream, StreamConfig, StreamOutput};

pub(crate) mod config;
pub(crate) mod stream;

pub(crate) fn main() {
    gstreamer::init().expect("Unable to initialize GStreamer!");

    create_stream(
        "",
        &StreamConfig {
            output: StreamOutput::RTMP {
                url: String::from(""),
                stream_key: String::from(""),
            },
        },
    )
    .expect("Unable to create stream");
}
