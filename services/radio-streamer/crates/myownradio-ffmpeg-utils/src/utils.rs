extern crate ffmpeg_next as ffmpeg;

use crate::INTERNAL_TIME_BASE;
use std::ops::{Deref, DerefMut};
use std::sync::Arc;
use std::time::Duration;

#[derive(Clone, PartialEq)]
pub struct Timestamp {
    value: i64,
    time_base: (i32, i32),
}

impl Timestamp {
    pub const ZERO: Timestamp = Timestamp::new(0, (1, 1));

    #[inline]
    pub const fn new(value: i64, time_base: (i32, i32)) -> Self {
        Self { value, time_base }
    }

    #[inline]
    pub const fn value(&self) -> i64 {
        self.value
    }

    #[inline]
    pub const fn time_base(&self) -> (i32, i32) {
        self.time_base
    }
}

impl Into<Duration> for &Timestamp {
    fn into(self) -> Duration {
        let secs = self.value as f64 * self.time_base.0 as f64 / self.time_base.1 as f64;

        Duration::from_secs_f64(secs.abs())
    }
}

impl Into<Timestamp> for Duration {
    fn into(self) -> Timestamp {
        Timestamp::new(self.as_nanos() as i64, (1, 1_000_000_000))
    }
}

impl Default for Timestamp {
    fn default() -> Self {
        Timestamp::new(0, INTERNAL_TIME_BASE)
    }
}

impl std::fmt::Debug for Timestamp {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        write!(
            f,
            "Timestamp({}, {}/{})",
            self.value, self.time_base.0, self.time_base.1
        )
    }
}

#[derive(Clone, Debug, PartialEq)]
pub struct AudioUnit {
    data: Arc<Vec<u8>>,
    duration: Timestamp,
    pts: Timestamp,
}

impl AudioUnit {
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

    pub fn set_pts(&mut self, pts: Timestamp) {
        self.pts = pts;
    }

    pub fn pts_as_duration(&self) -> Duration {
        self.pts().into()
    }

    pub fn is_empty(&self) -> bool {
        self.data.is_empty()
    }
}

#[derive(Clone, Debug, PartialEq)]
pub struct Frame(AudioUnit);

impl Frame {
    pub fn new(pts: Timestamp, duration: Timestamp, data: Vec<u8>) -> Self {
        Self(AudioUnit::new(pts, duration, data))
    }
}

impl Deref for Frame {
    type Target = AudioUnit;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl DerefMut for Frame {
    fn deref_mut(&mut self) -> &mut Self::Target {
        &mut self.0
    }
}

#[derive(Clone, Debug, PartialEq)]
pub struct Packet(AudioUnit);

impl Packet {
    pub(crate) fn new(pts: Timestamp, duration: Timestamp, data: Vec<u8>) -> Self {
        Self(AudioUnit::new(pts, duration, data))
    }
}

impl Deref for Packet {
    type Target = AudioUnit;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl DerefMut for Packet {
    fn deref_mut(&mut self) -> &mut Self::Target {
        &mut self.0
    }
}
