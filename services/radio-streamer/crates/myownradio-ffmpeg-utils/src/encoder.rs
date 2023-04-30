extern crate ffmpeg_next as ffmpeg;

use crate::utils::{convert_frame_to_packed, convert_frame_to_planar, Frame};
use crate::INTERNAL_SAMPLING_FREQUENCY;
use ffmpeg::encoder::find_by_name;
use ffmpeg::format::sample::Type::{Packed, Planar};
use ffmpeg::format::Sample::I16;
use ffmpeg::frame::Audio;
use ffmpeg::{codec, encoder, ChannelLayout, Packet};
use ffmpeg_next::Codec;

#[derive(Debug)]
enum Format {
    MP3,
    AAC,
}

impl Format {
    fn encoder_name(&self) -> &'static str {
        match self {
            Format::MP3 => "libmp3lame",
            Format::AAC => "libfdk_aac",
        }
    }

    fn find_codec(&self) -> Option<Codec> {
        find_by_name(self.encoder_name())
    }

    fn setup_encoder(&self, encoder: &mut encoder::audio::Audio, bit_rate: usize) {
        encoder.set_bit_rate(bit_rate);
        encoder.set_rate(INTERNAL_SAMPLING_FREQUENCY as i32);
        encoder.set_channel_layout(ChannelLayout::STEREO);
        encoder.set_format(match self {
            Format::MP3 => I16(Planar),
            Format::AAC => I16(Packed),
        });
    }

    fn prepare_frame(&self, frame: Frame) -> Audio {
        match self {
            Format::MP3 => convert_frame_to_planar(frame),
            Format::AAC => convert_frame_to_packed(frame),
        }
    }
}

struct AudioEncoder {
    encoder: encoder::audio::Encoder,
    format: Format,
}

#[derive(Debug, thiserror::Error)]
enum AudioEncoderError {
    #[error("Unable to find codec: {0:?}")]
    CodecError(&'static str),
    #[error("Audio encoding failed: {0:?}")]
    EncodingError(ffmpeg::Error),
}

impl AudioEncoder {
    fn open(format: Format, bitrate: usize) -> Result<Self, AudioEncoderError> {
        let ctx = codec::Context::new();
        let mut encoder = ctx
            .encoder()
            .audio()
            .map_err(|error| AudioEncoderError::EncodingError(error))?;

        format.setup_encoder(&mut encoder, bitrate);

        let codec = format
            .find_codec()
            .ok_or_else(|| AudioEncoderError::CodecError(format.encoder_name()))?;
        let encoder = encoder
            .open_as(codec)
            .map_err(|error| AudioEncoderError::EncodingError(error))?;

        Ok(Self { encoder, format })
    }

    fn send_frame_to_encoder(&mut self, frame: Frame) -> Result<(), AudioEncoderError> {
        let audio = self.format.prepare_frame(frame);

        self.encoder
            .send_frame(&audio)
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
            frames.push(encoded.clone());
        }

        Ok(frames)
    }
}

#[cfg(test)]
mod tests {
    extern crate ffmpeg_next as ffmpeg;

    use crate::encoder::{AudioEncoder, Format};
    use crate::{Frame, Timestamp, INTERNAL_SAMPLE_SIZE};

    #[ctor::ctor]
    fn init() {
        ffmpeg::init().expect("Unable to initialize FFmpeg");
        // ffmpeg::log::set_level(ffmpeg::log::Level::Verbose);
    }

    #[actix_rt::test]
    async fn test_encoding() {
        let test_cases = vec![
            (Format::MP3, 128_000, 427, 10372),
            (Format::AAC, 64_000, 481, 10197),
        ];
        let raw_time_base = (1, 48_000);
        let raw_audio = include_bytes!("../tests/fixtures/test_file.raw");

        for (format, bit_rate, expected_packets, expected_last_pts) in test_cases {
            let mut encoder =
                AudioEncoder::open(format, bit_rate).expect("Unable to construct encoder");

            let mut encoded_packets = vec![];
            for (i, chunk) in raw_audio.chunks_exact(1024).enumerate() {
                let chunk_len = (chunk.len() / INTERNAL_SAMPLE_SIZE) as i64;
                let chunk_id = i as i64;

                let frame = Frame::new(
                    Timestamp::new(chunk_id * chunk_len, raw_time_base),
                    Timestamp::new(chunk_len, raw_time_base),
                    chunk.to_vec(),
                );

                encoder.send_frame_to_encoder(frame).unwrap();
                encoded_packets.append(&mut encoder.receive_encoded_packets().unwrap());
            }

            encoder.send_eof_to_encoder().unwrap();
            encoded_packets.append(&mut encoder.receive_encoded_packets().unwrap());

            assert_eq!(expected_packets, encoded_packets.len());
            assert_eq!(
                expected_last_pts,
                encoded_packets.last().and_then(|l| l.pts()).unwrap()
            );
        }
    }
}
