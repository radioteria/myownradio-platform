use std::time::Duration;

pub(crate) const AUDIO_SAMPLING_FREQUENCY: usize = 48_000;
pub(crate) const AUDIO_CHANNELS_NUMBER: usize = 2;

pub(crate) const PRELOAD_TIME: Duration = Duration::from_millis(2500);
