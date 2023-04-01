use actix_web::web::Bytes;
use ffmpeg_next::Rescale;
#[macro_export]
macro_rules! unwrap_or_return {
    ($x:expr, $r:expr) => {{
        match $x {
            Ok(value) => value,
            Err(_) => return $r,
        }
    }};
    ($x:expr) => {
        unwrap_or_return!($x, ())
    };
}

/// Converts a slice of signed 16-bit integers to a vector of unsigned 8-bit integers,
/// with little-endian byte order.
///
/// # Arguments
///
/// * `i16s`: A slice of signed 16-bit integers to convert. Each pair of integers represents
///           the left and right channel samples of an audio frame.
///
/// # Returns
///
/// A new vector of unsigned 8-bit integers, with little-endian byte order. Each pair of
/// integers in the input slice is split into four 8-bit integers in the output vector,
/// with the lower and upper bytes of each 16-bit integer swapped.
///
/// # Examples
///
/// ```
/// let samples = vec![(0x1234, 0x5678), (0x9ABC, 0xDEF0)];
/// let bytes = convert_sample_to_byte_data(&samples);
/// assert_eq!(bytes, vec![0x34, 0x12, 0x78, 0x56, 0xBC, 0x9A, 0xF0, 0xDE]);
/// ```
pub(crate) fn convert_sample_to_byte_data(i16s: &[(i16, i16)]) -> Vec<u8> {
    let mut u8s = Vec::new();

    for &(left, right) in i16s {
        u8s.push((left & 0xFF) as u8);
        u8s.push(((left >> 8) & 0xFF) as u8);
        u8s.push((right & 0xFF) as u8);
        u8s.push(((right >> 8) & 0xFF) as u8);
    }

    u8s
}

/// Rescales the timestamp of an audio frame using the given source and destination time bases.
///
/// # Arguments
///
/// * `frame` - A mutable reference to the audio frame to be rescaled.
/// * `source` - The time base of the source video stream.
/// * `dest` - The desired time base for the rescaled frame.
///
/// # Example
///
/// ```
/// use ffmpeg_next::Rational;
/// use ffmpeg_next::frame::Audio;
///
/// let mut audio_frame = Audio::new();
/// let source_timebase = Rational::new(1, 1000);
/// let dest_timebase = Rational::new(1, 48000);
///
/// rescale_audio_frame_ts(&mut audio_frame, source_timebase, dest_timebase);
/// ```
pub(crate) fn rescale_audio_frame_ts(
    frame: &mut ffmpeg_next::frame::Audio,
    source: ffmpeg_next::Rational,
    dest: ffmpeg_next::Rational,
) {
    let rescaled_ts = frame.pts().map(|pts| pts.rescale(source, dest));
    frame.set_pts(rescaled_ts);
}
