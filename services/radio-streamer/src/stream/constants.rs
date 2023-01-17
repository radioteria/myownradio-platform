use std::time::Duration;

pub(crate) const AUDIO_SAMPLING_FREQUENCY: usize = 48_000;
pub(crate) const AUDIO_CHANNELS_NUMBER: usize = 2;

/// Duration of time in which the stream player sends initial
/// buffers in real-time before slowing down to its normal real-time pace.
pub(crate) const REALTIME_STARTUP_BUFFER_TIME: Duration = Duration::from_millis(2500);
