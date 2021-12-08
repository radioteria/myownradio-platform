use crate::audio_formats::AudioFormat;
use crate::metrics::Metrics;
use crate::stream::channel_encoder::ChannelEncoder;
use slog::Logger;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

#[derive(Debug, Hash, Eq, PartialEq, Clone)]
struct EncoderEntry(usize, Option<String>, AudioFormat);

#[derive(Clone)]
pub(crate) struct EncoderRegistry {}

struct Inner {
    path_to_ffmpeg: String,
    logger: Logger,
    metrics: Arc<Metrics>,
    encoders_map: Arc<Mutex<HashMap<EncoderEntry, ChannelEncoder>>>,
}

impl Drop for Inner {
    fn drop(&mut self) {
        todo!()
    }
}

impl Inner {}
