extern crate ffmpeg_next as ffmpeg;

use crate::utils::Frame;
use crate::{INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_FREQUENCY};
use ffmpeg::codec::traits::Encoder;
use ffmpeg::format::sample::Type::Planar;
use ffmpeg::format::Sample::I16;
use ffmpeg::frame::Audio;
use ffmpeg::{codec, encoder, ChannelLayout, Packet};

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
        let codec = name
            .encoder()
            .ok_or_else(|| AudioEncoderError::CodecError(name.to_string()))?;

        let ctx = codec::Context::new();
        let mut encoder = ctx.encoder().audio().unwrap();

        encoder.set_bit_rate(bitrate);
        encoder.set_format(I16(Planar));
        encoder.set_rate(INTERNAL_SAMPLING_FREQUENCY as i32);
        encoder.set_channel_layout(ChannelLayout::STEREO);

        let encoder = encoder.open_as(codec).unwrap();

        Ok(Self { encoder })
    }

    fn send_frame_to_encoder(&mut self, frame: Frame) -> Result<(), AudioEncoderError> {
        let mut ff_frame = Audio::empty();

        ff_frame.set_rate(INTERNAL_SAMPLING_FREQUENCY as u32);
        ff_frame.set_channel_layout(ChannelLayout::STEREO);
        ff_frame.set_samples(frame.data().len() / INTERNAL_SAMPLE_SIZE);
        // ff_frame.set_format(I16(Planar));
        ff_frame.data_mut(1).copy_from_slice(frame.data());

        self.encoder
            .send_frame(&ff_frame)
            .map_err(|error| AudioEncoderError::EncodingError(error))?;

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
        let frame = Frame::new(
            Timestamp::default(),
            Timestamp::default(),
            Vec::with_capacity(1024),
        );

        let mut encoder =
            AudioEncoder::new("libmp3lame", 128_000).expect("Unable to construct encoder");

        encoder.send_frame_to_encoder(frame).unwrap();
    }
}
