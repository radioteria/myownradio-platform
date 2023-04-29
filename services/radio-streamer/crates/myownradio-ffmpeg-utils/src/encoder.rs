extern crate ffmpeg_next as ffmpeg;

use crate::utils::Frame;
use crate::{INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_FREQUENCY};
use ffmpeg::codec::traits::Encoder;
use ffmpeg::format::sample::Type::{Packed, Planar};
use ffmpeg::format::Sample::I16;
use ffmpeg::frame::Audio;
use ffmpeg::{codec, encoder, ChannelLayout, Packet};
use ffmpeg_next::format::Sample;
use std::ops::DerefMut;

struct AudioEncoder {
    encoder: encoder::audio::Encoder,
}

#[derive(Debug, thiserror::Error)]
enum AudioEncoderError {
    #[error("Unable to find codec: {0}")]
    CodecError(String),
    #[error("Audio encoding failed: {0}")]
    EncodingError(ffmpeg::Error),
}

impl AudioEncoder {
    fn new(name: &str, bitrate: usize) -> Result<Self, AudioEncoderError> {
        let ctx = codec::Context::new();
        let mut encoder = ctx.encoder().audio().unwrap();

        encoder.set_bit_rate(bitrate);
        encoder.set_format(I16(Planar));
        encoder.set_rate(INTERNAL_SAMPLING_FREQUENCY as i32);
        encoder.set_channel_layout(ChannelLayout::STEREO);

        let codec = name.encoder().unwrap();
        let encoder = encoder.open_as(codec).unwrap();

        Ok(Self { encoder })
    }

    fn send_frame_to_encoder(&mut self, frame: Frame) -> Result<(), AudioEncoderError> {
        let mut ff_frame = Audio::empty();

        let samples = frame.data().len() / INTERNAL_SAMPLE_SIZE;
        let pts = frame.pts_as_duration().as_millis() as i64;

        ff_frame.set_pts(Some(pts));
        ff_frame.set_format(I16(Packed));
        ff_frame.set_samples(samples);
        ff_frame.set_channels(2);
        ff_frame.set_rate(INTERNAL_SAMPLING_FREQUENCY as u32);

        unsafe {
            (*ff_frame.as_mut_ptr()).linesize[0] = frame.data().len() as i32;
            (*ff_frame.as_mut_ptr()).data[0] = frame.data().as_ptr() as *mut u8;
        };

        self.encoder
            .send_frame(&ff_frame)
            .expect("Unable to send frame");

        Ok(())
    }

    fn send_eof_to_encoder(&mut self) -> Result<(), AudioEncoderError> {
        self.encoder
            .send_eof()
            .map_err(|error| AudioEncoderError::EncodingError(error))?;
        Ok(())
    }

    fn receive_encoded_packets(&mut self) -> Result<Vec<Packet>, AudioEncoderError> {
        let mut frames = vec![];

        let mut encoded = Packet::empty();
        while self.encoder.receive_packet(&mut encoded).is_ok() {
            encoded.set_stream(0);
            frames.push(encoded.clone());
        }

        Ok(frames)
    }
}

#[cfg(test)]
mod tests {
    extern crate ffmpeg_next as ffmpeg;

    use crate::encoder::AudioEncoder;
    use crate::{Frame, Timestamp};

    #[ctor::ctor]
    fn init() {
        ffmpeg::init().expect("Unable to initialize FFmpeg");
        // ffmpeg::log::set_level(ffmpeg::log::Level::Trace);
    }

    #[actix_rt::test]
    async fn test_encoding_raw_audio_samples_to_mp3() {
        let frame1 = Frame::new(
            Timestamp::default(),
            Timestamp::default(),
            (0..4096).map(|_| 0u8).collect(),
        );

        let mut encoder =
            AudioEncoder::new("libmp3lame", 128_000).expect("Unable to construct encoder");

        encoder.send_frame_to_encoder(frame1).unwrap();
        encoder.send_eof_to_encoder().unwrap();
        let packets = encoder.receive_encoded_packets().unwrap();

        assert_eq!(1, packets.len());
        assert_eq!(
            vec![
                255, 251, 148, 100, 0, 15, 240, 0, 0, 105, 0, 0, 0, 8, 0, 0, 13, 32, 0, 0, 1, 0, 0,
                1, 164, 0, 0, 0, 32, 0, 0, 52, 128, 0, 0, 4, 76, 65, 77, 69, 51, 46, 49, 48, 48,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85, 85,
                85, 85, 85
            ],
            packets[0].data().unwrap().to_vec()
        );
    }
}
