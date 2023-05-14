extern crate ffmpeg_next as ffmpeg;

use crate::{INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_FREQUENCY, INTERNAL_TIME_BASE};
use ffmpeg::frame::Audio;
use ffmpeg_next::format::sample::Type::{Packed, Planar};
use ffmpeg_next::format::Sample::I16;
use ffmpeg_next::ChannelLayout;
use std::fmt::Debug;
use std::ops::{Deref, DerefMut};
use std::sync::Arc;
use std::time::Duration;

#[derive(Clone, Debug, PartialEq)]
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

// @todo millis -> micros
impl Into<Timestamp> for Duration {
    fn into(self) -> Timestamp {
        Timestamp::new(self.as_millis() as i64, INTERNAL_TIME_BASE)
    }
}

impl Default for Timestamp {
    fn default() -> Self {
        Timestamp::new(0, INTERNAL_TIME_BASE)
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

pub(crate) fn convert_frame_to_planar(src_frame: Frame) -> Audio {
    let pts = src_frame.pts_as_duration().as_millis() as i64;
    let data_length = src_frame.data().len();
    let data = src_frame.data();
    let samples = data_length / INTERNAL_SAMPLE_SIZE;

    let mut dst_frame = Audio::new(I16(Planar), samples, ChannelLayout::STEREO);

    dst_frame.set_pts(Some(pts));
    dst_frame.set_channels(2);
    dst_frame.set_rate(INTERNAL_SAMPLING_FREQUENCY as u32);

    let mut left_data: Vec<u8> = vec![];
    let mut right_data: Vec<u8> = vec![];

    for chunk in data.chunks_exact(4) {
        left_data.push(chunk[0]);
        left_data.push(chunk[1]);
        right_data.push(chunk[2]);
        right_data.push(chunk[3]);
    }

    let left_data = Box::into_raw(left_data.into_boxed_slice());
    let right_data = Box::into_raw(right_data.into_boxed_slice());

    unsafe {
        (*dst_frame.as_mut_ptr()).linesize[0] = 1;

        (*dst_frame.as_mut_ptr()).data[0] = left_data as *mut u8;
        (*dst_frame.as_mut_ptr()).data[1] = right_data as *mut u8;
    };

    dst_frame
}

pub(crate) fn convert_frame_to_packed(src_frame: Frame) -> Audio {
    let pts = src_frame.pts_as_duration().as_millis() as i64;
    let data_length = src_frame.data().len();
    let samples = data_length / INTERNAL_SAMPLE_SIZE;

    let mut dst_frame = Audio::new(I16(Packed), samples, ChannelLayout::STEREO);

    dst_frame.set_pts(Some(pts));
    dst_frame.set_channels(2);
    dst_frame.set_rate(INTERNAL_SAMPLING_FREQUENCY as u32);

    let data = Box::into_raw(src_frame.data().as_ref().clone().into_boxed_slice());

    unsafe {
        (*dst_frame.as_mut_ptr()).linesize[0] = 1;

        (*dst_frame.as_mut_ptr()).data[0] = data as *mut u8;
    };

    dst_frame
}
