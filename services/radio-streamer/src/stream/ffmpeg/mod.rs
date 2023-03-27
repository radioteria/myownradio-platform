use ffmpeg_next::{ChannelLayout, Rational};

mod decoder;
mod utils;

pub(crate) use decoder::{decode_audio_file, AudioFileDecodeError};

const INTERNAL_CHANNEL_LAYOUT: ChannelLayout = ChannelLayout::STEREO;
const INTERNAL_SAMPLING_RATE: i32 = 48_000;

const RESAMPLER_TIME_BASE: Rational = Rational(1, INTERNAL_SAMPLING_RATE);
