use ffmpeg_next::{ChannelLayout, Rational};

mod decoder;
mod utils;

pub(crate) use decoder::{decode_audio_file, AudioDecoderError};

const INTERNAL_CHANNELS_COUNT: i32 = 2;
const INTERNAL_SAMPLING_RATE: i32 = 48_000;
/// Number of bytes required to represent 16-bit stereo sample as four 8-bit integers.
const INTERNAL_SAMPLE_SIZE: usize = 4;

const RESAMPLER_TIMEBASE: Rational = Rational(1, INTERNAL_SAMPLING_RATE);
