pub(crate) const AUDIO_SAMPLING_FREQUENCY: usize = 48_000;
pub(crate) const AUDIO_BYTES_PER_SAMPLE: usize = 2;
pub(crate) const AUDIO_CHANNELS: usize = 2;
pub(crate) const AUDIO_BYTES_PER_SECOND: usize =
    AUDIO_SAMPLING_FREQUENCY * AUDIO_BYTES_PER_SAMPLE * AUDIO_CHANNELS;
