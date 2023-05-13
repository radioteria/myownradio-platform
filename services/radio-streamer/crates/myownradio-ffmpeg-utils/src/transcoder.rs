extern crate ffmpeg_next as ffmpeg;

use crate::ffmpeg::{
    open_input, setup_audio_decoder, setup_audio_encoder, setup_resampling_filter, OpenInputError,
    SetupAudioDecoderError, SetupAudioEncoderError, SetupResamplingFilterError,
};
use crate::utils;
use ffmpeg::decoder;
use ffmpeg::format::context::input::PacketIter;
use ffmpeg::frame::Audio;
use ffmpeg::{encoder, filter, Packet};
use ffmpeg_next::format;
use ffmpeg_next::format::sample::Type::{Packed, Planar};
use ffmpeg_next::format::Sample::I16;
use iter_tools::dependency::itertools::Iterate;
use std::time::Duration;
use tracing::trace;

trait SamplingRate {
    fn sampling_rate(&self) -> u32;
}

trait Bitrate {
    fn bitrate(&self) -> usize;
}

trait EncoderName {
    fn encoder_name(&self) -> &'static str;
}

#[derive(Debug)]
pub enum OutputFormat {
    MP3 { bit_rate: usize, sampling_rate: u32 },
    AAC { bit_rate: usize, sampling_rate: u32 },
}

impl SamplingRate for OutputFormat {
    fn sampling_rate(&self) -> u32 {
        match *self {
            OutputFormat::MP3 { sampling_rate, .. } => sampling_rate,
            OutputFormat::AAC { sampling_rate, .. } => sampling_rate,
        }
    }
}

impl Bitrate for OutputFormat {
    fn bitrate(&self) -> usize {
        match *self {
            OutputFormat::MP3 { bit_rate, .. } => bit_rate,
            OutputFormat::AAC { bit_rate, .. } => bit_rate,
        }
    }
}

impl EncoderName for OutputFormat {
    fn encoder_name(&self) -> &'static str {
        match self {
            Self::MP3 { .. } => "libmp3lame",
            Self::AAC { .. } => "libfdk_aac",
        }
    }
}

#[derive(Debug, thiserror::Error)]
pub enum AudioTranscoderCreationError {
    #[error("Unable to open input: {0}")]
    OpenInputError(#[from] OpenInputError),
    #[error("Unable to initialize audio decoder: {0}")]
    SetupAudioDecoderError(#[from] SetupAudioDecoderError),
    #[error("Unable to initialize audio encoder: {0}")]
    SetupAudioEncoderError(#[from] SetupAudioEncoderError),
    #[error("Unable to initialize resampling filter: {0}")]
    SetupResamplingFilterError(#[from] SetupResamplingFilterError),
    #[error("Unable to open input file: {0}")]
    FileOpeningError(ffmpeg_next::Error),
    #[error("Audio stream not found")]
    AudioStreamNotFound,
    #[error("Decoder failed: {0}")]
    DecoderError(ffmpeg_next::Error),
    #[error("Resampler failed: {0}")]
    ResamplerError(ffmpeg_next::Error),
    #[error("Encoder failed: {0}")]
    EncoderError(ffmpeg_next::Error),
    #[error("Unable to initialize codec: {0}")]
    CodecNotFound(&'static str),
}

pub struct AudioTranscoder {
    input: format::context::Input,
    input_index: usize,
    resampler: filter::Graph,
    encoder: encoder::Audio,
    decoder: decoder::Audio,
    is_eof: bool,
    transcoded_packet_number: usize,
}

#[derive(Debug, thiserror::Error)]
pub enum TranscodeError {
    #[error("FFmpeg returned error: {0}")]
    FFmpegError(#[from] ffmpeg_next::Error),
}

impl AudioTranscoder {
    pub fn create(
        source_url: &str,
        offset: &Duration,
        output_format: &OutputFormat,
    ) -> Result<Self, AudioTranscoderCreationError> {
        let mut input = open_input(source_url, offset)?;

        let (input_index, decoder) = setup_audio_decoder(&mut input)?;
        let resampler = setup_resampling_filter(
            output_format.sampling_rate(),
            match output_format {
                OutputFormat::MP3 { .. } => I16(Planar),
                OutputFormat::AAC { .. } => I16(Packed),
            },
            &decoder,
        )?;
        let encoder = setup_audio_encoder(
            output_format.encoder_name(),
            output_format.bitrate(),
            output_format.sampling_rate(),
        )?;

        Ok(Self {
            input,
            input_index,
            decoder,
            resampler,
            encoder,
            is_eof: false,
            transcoded_packet_number: 0,
        })
    }

    pub fn receive_next_transcoded_packets(
        &mut self,
    ) -> Result<Option<Vec<utils::Packet>>, TranscodeError> {
        match self.get_packet_from_input() {
            Some(packet) => {
                trace!("Send 1 packet to decoder");
                self.send_packet_to_decoder(&packet)?;

                let encoded_packets = self.receive_and_process_decoded_frames()?;
                trace!("Read encoded packets: {}", encoded_packets.len());

                let prepared_packets: Vec<_> = encoded_packets
                    .into_iter()
                    .map(|pkt| self.prepare_packet(pkt))
                    .collect();

                self.transcoded_packet_number += prepared_packets.len();

                Ok(Some(prepared_packets))
            }
            None if self.is_eof => Ok(None),
            None => {
                let mut final_encoded_packets = vec![];

                trace!("Send EOF to decoder");
                self.send_eof_to_decoder()?;
                final_encoded_packets.append(&mut self.receive_and_process_decoded_frames()?);

                trace!("Send flush to resampler");
                self.flush_resampler()?;
                final_encoded_packets.append(&mut self.get_and_process_resampled_frames()?);

                trace!("Send EOF to encoder");
                self.send_eof_to_encoder()?;
                final_encoded_packets.append(&mut self.receive_encoded_packets()?);

                self.is_eof = true;

                let prepared_packets: Vec<_> = final_encoded_packets
                    .into_iter()
                    .map(|pkt| self.prepare_packet(pkt))
                    .collect();

                self.transcoded_packet_number += prepared_packets.len();

                Ok(Some(prepared_packets))
            }
        }
    }

    pub fn transcoded_packet_number(&self) -> usize {
        self.transcoded_packet_number
    }

    fn prepare_packet(&self, pkt: Packet) -> utils::Packet {
        let pts = pkt.pts().unwrap_or_default();
        let duration = pkt.duration();
        let output_time_base = (1, self.encoder.rate() as i32);
        let data = pkt.data().unwrap_or_default().to_vec();

        utils::Packet::new(
            utils::Timestamp::new(pts, output_time_base),
            utils::Timestamp::new(duration, output_time_base),
            data,
        )
    }

    fn receive_and_process_decoded_frames(&mut self) -> Result<Vec<Packet>, ffmpeg_next::Error> {
        let mut packets = vec![];

        let mut decoded = Audio::empty();
        while self.decoder.receive_frame(&mut decoded).is_ok() {
            trace!("Decoded 1 frame");
            let timestamp = decoded.timestamp();
            decoded.set_pts(timestamp);
            trace!("Send 1 frame to resampler");
            self.send_frame_to_resampler(&decoded)?;
            packets.append(&mut self.get_and_process_resampled_frames()?);
        }

        Ok(packets)
    }

    fn get_and_process_resampled_frames(&mut self) -> Result<Vec<Packet>, ffmpeg_next::Error> {
        let mut packets = vec![];

        let mut resampled = Audio::empty();
        while self
            .resampler
            .get("out")
            .unwrap()
            .sink()
            .samples(&mut resampled, 1024)
            .is_ok()
        {
            trace!("Resampled 1 frame");
            self.send_frame_to_encoder(&resampled)?;
            packets.append(&mut self.receive_encoded_packets()?);
        }

        Ok(packets)
    }

    fn get_packet_from_input(&mut self) -> Option<Packet> {
        while let Some((stream, mut pkt)) = self.input.packets().next() {
            if stream.index() == self.input_index {
                pkt.rescale_ts(stream.time_base(), self.decoder.time_base());
                return Some(pkt);
            }
        }

        None
    }

    fn send_packet_to_decoder(&mut self, packet: &Packet) -> Result<(), ffmpeg_next::Error> {
        self.decoder.send_packet(packet)?;
        Ok(())
    }

    fn send_eof_to_decoder(&mut self) -> Result<(), ffmpeg_next::Error> {
        self.decoder.send_eof()?;
        Ok(())
    }

    fn receive_decoded_frames(&mut self) -> Result<Vec<Audio>, ffmpeg_next::Error> {
        let mut frames = vec![];

        let mut frame = Audio::empty();
        while self.decoder.receive_frame(&mut frame).is_ok() {
            let timestamp = frame.timestamp();
            frame.set_pts(timestamp);
            frames.push(frame.clone());
        }

        Ok(frames)
    }

    fn send_frame_to_resampler(&mut self, frame: &Audio) -> Result<(), ffmpeg_next::Error> {
        self.resampler
            .get("in")
            .expect("Unable to get 'in' pad on filter")
            .source()
            .add(frame)?;

        Ok(())
    }

    fn flush_resampler(&mut self) -> Result<(), ffmpeg_next::Error> {
        self.resampler
            .get("in")
            .expect("Unable to get 'in' pad on filter")
            .source()
            .flush()?;

        Ok(())
    }

    fn receive_resampled_frames(&mut self) -> Result<Vec<Audio>, ffmpeg_next::Error> {
        let mut frames = vec![];

        let mut buffer = Audio::empty();
        while self
            .resampler
            .get("out")
            .expect("Unable to get 'out' pad on filter")
            .sink()
            .samples(&mut buffer, 1024)
            .is_ok()
        {
            frames.push(buffer.clone());
        }

        Ok(frames)
    }

    fn send_frame_to_encoder(&mut self, frame: &Audio) -> Result<(), ffmpeg_next::Error> {
        trace!("Send 1 frame to encoder");
        self.encoder.send_frame(frame)?;
        Ok(())
    }

    fn send_eof_to_encoder(&mut self) -> Result<(), ffmpeg_next::Error> {
        trace!("Send EOF to encoder");
        self.encoder.send_eof()?;
        Ok(())
    }

    fn receive_encoded_packets(&mut self) -> Result<Vec<Packet>, ffmpeg_next::Error> {
        let mut packets = vec![];

        let mut buffer = Packet::empty();
        while self.encoder.receive_packet(&mut buffer).is_ok() {
            trace!("Received 1 encoded packet");
            buffer.set_stream(0);
            packets.push(buffer.clone());
        }

        Ok(packets)
    }
}

#[cfg(test)]
mod tests {
    extern crate ffmpeg_next as ffmpeg;

    use crate::transcoder::{AudioTranscoder, OutputFormat};
    use std::time::Duration;
    use tracing::warn;

    #[ctor::ctor]
    fn init() {
        ffmpeg::init().expect("Unable to initialize FFmpeg");
        // ffmpeg::log::set_level(ffmpeg::log::Level::Trace);
    }

    #[actix_rt::test]
    #[tracing_test::traced_test]
    async fn test_transcoding() {
        let test_file = "tests/fixtures/test_file.wav";
        let test_cases = vec![
            (
                OutputFormat::MP3 {
                    bit_rate: 128_000,
                    sampling_rate: 48_000,
                },
                427,
                489647,
            ),
            (
                OutputFormat::AAC {
                    bit_rate: 64_000,
                    sampling_rate: 48_000,
                },
                481,
                489472,
            ),
        ];
        let offset = Duration::from_millis(0);

        for (format, expected_packets, expected_last_pts) in test_cases {
            let mut actual_packets = 0;
            let mut actual_last_pts = 0;

            let mut transcoder = AudioTranscoder::create(test_file, &offset, &format).unwrap();

            while let Ok(Some(packets)) = transcoder.receive_next_transcoded_packets() {
                actual_packets += packets.len();
                actual_last_pts = packets.last().map(|p| p.pts().value()).unwrap_or_default()
            }

            assert_eq!(expected_packets, actual_packets);
            assert_eq!(expected_last_pts, actual_last_pts);
        }
    }
}
