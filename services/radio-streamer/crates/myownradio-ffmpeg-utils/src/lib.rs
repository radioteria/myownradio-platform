mod decoder;
mod generator;
mod utils;

pub use decoder::{decode_audio_file, AudioDecoderError};
pub use generator::generate_silence;
pub use utils::{Frame, Timestamp};

// The number of audio channels used internally by the program.
const INTERNAL_CHANNELS_NUMBER: i64 = 2;

// The sampling rate used internally by the program, in Hz.
const INTERNAL_SAMPLING_FREQUENCY: i64 = 48_000;

// The number of bytes required to represent a single 16-bit stereo audio sample as four 8-bit integers.
const INTERNAL_SAMPLE_SIZE: usize = 4;

// The timebase used internally by the program, expressed as a ratio of time units.
const INTERNAL_TIME_BASE: (i32, i32) = (1, 1000);

// The timebase used by the audio resampler, expressed as a ratio of time units.
const RESAMPLER_TIME_BASE: (i32, i32) = (1, INTERNAL_SAMPLING_FREQUENCY as i32);
