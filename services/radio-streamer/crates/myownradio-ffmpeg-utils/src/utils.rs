use crate::INTERNAL_TIME_BASE;
use ffmpeg_next::Rescale;
use std::fmt::Debug;
use std::sync::Arc;
use std::time::Duration;

#[derive(Clone, Debug, PartialEq)]
pub struct Timestamp {
    value: i64,
    time_base: (i32, i32),
}

impl Timestamp {
    pub fn new(value: i64, time_base: (i32, i32)) -> Self {
        Self { value, time_base }
    }

    pub fn value(&self) -> i64 {
        self.value
    }

    pub fn time_base(&self) -> (i32, i32) {
        self.time_base
    }
}

impl Into<Duration> for &Timestamp {
    fn into(self) -> Duration {
        let millis = self.value.rescale(self.time_base, INTERNAL_TIME_BASE) as u64;
        Duration::from_millis(millis)
    }
}

impl Default for Timestamp {
    fn default() -> Self {
        Timestamp::new(0, INTERNAL_TIME_BASE)
    }
}

#[derive(Clone, Debug, PartialEq)]
pub struct Frame {
    data: Arc<Vec<u8>>,
    duration: Timestamp,
    pts: Timestamp,
}

impl Frame {
    pub(crate) fn new(pts: Timestamp, duration: Timestamp, data: Vec<u8>) -> Self {
        let data = Arc::new(data);

        Self {
            pts,
            duration,
            data,
        }
    }

    pub fn data(&self) -> &Arc<Vec<u8>> {
        &self.data
    }

    pub fn duration(&self) -> &Timestamp {
        &self.duration
    }

    pub fn pts(&self) -> &Timestamp {
        &self.pts
    }

    pub fn is_empty(&self) -> bool {
        self.data.is_empty()
    }
}

/// Rescales the timestamp of an audio frame using the given source and destination time bases.
pub(crate) fn rescale_audio_frame_ts(
    frame: &mut ffmpeg_next::frame::Audio,
    source: ffmpeg_next::Rational,
    dest: ffmpeg_next::Rational,
) {
    let rescaled_ts = frame.pts().map(|pts| pts.rescale(source, dest));
    frame.set_pts(rescaled_ts);
}
