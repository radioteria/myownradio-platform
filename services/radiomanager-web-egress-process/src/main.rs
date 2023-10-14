use crate::config::{Config, VideoAcceleration};
use crate::stream::{Stream, StreamConfig, StreamEvent, StreamOutput, VideoEncoder};
use std::sync::mpsc::channel;
use tracing::error;

pub(crate) mod config;
pub(crate) mod gstreamer_utils;
pub(crate) mod stream;
pub(crate) mod stream_utils;

pub(crate) fn main() {
    tracing_subscriber::fmt::init();

    let config = Config::from_env();

    gstreamer::init().expect("Unable to initialize GStreamer!");

    let (event_sender, event_receiver) = channel();

    let stream = Stream::create(
        config.webpage_url,
        &StreamConfig {
            output: StreamOutput::RTMP {
                url: config.rtmp_url,
                stream_key: config.rtmp_stream_key,
            },
            video_width: config.video.width,
            video_height: config.video.height,
            video_bitrate: config.video.bitrate,
            video_framerate: config.video.framerate,
            video_profile: config.video.profile,
            video_encoder: match config.video_acceleration {
                None => VideoEncoder::Software,
                Some(VideoAcceleration::VAAPI) => VideoEncoder::VA,
            },
            audio_bitrate: config.audio.bitrate,
            cef_gpu_enabled: config.cef_gpu_enabled,
        },
        event_sender,
    )
    .expect("Unable to create stream");

    while let Ok(event) = event_receiver.recv() {
        match event {
            StreamEvent::Error(error) => {
                error!("Error happened while streaming: {:?}", error);
                break;
            }
        }
    }

    drop(stream);
}
