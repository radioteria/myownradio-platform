use std::time::Duration;

pub const RAW_AUDIO_STEREO_BYTE_RATE: usize = 176400;
pub const PREFETCH_TIME: Duration = Duration::from_secs(3);
pub const ALLOWED_DELAY_FOR_PRE_SPAWNED_DECODER: Duration = Duration::from_secs(1);
