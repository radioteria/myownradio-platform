extern crate ffmpeg_next as ffmpeg;

use crate::ffmpeg::{
    open_input, setup_audio_decoder, setup_audio_encoder, setup_resampling_filter, OpenInputError,
    SetupAudioDecoderError, SetupAudioEncoderError, SetupResamplingFilterError,
};
use crate::{utils, Timestamp};
use ffmpeg::decoder;
use ffmpeg::format;
use ffmpeg::format::context::input::PacketIter;
use ffmpeg::format::sample::Type::{Packed, Planar};
use ffmpeg::format::Sample::I16;
use ffmpeg::frame::Audio;
use ffmpeg::{encoder, filter, Packet};
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

pub struct Stats {
    pub first_decoded_packet_pts: Option<Timestamp>,
    pub last_decoded_packet_pts: Option<Timestamp>,
    pub last_decoded_packet_duration: Option<Timestamp>,
    pub first_encoded_packet_pts: Option<Timestamp>,
    pub last_encoded_packet_pts: Option<Timestamp>,
    pub last_encoded_packet_duration: Option<Timestamp>,
    pub decoded_packets_number: usize,
    pub encoded_packets_number: usize,
}

pub struct AudioTranscoder {
    input: format::context::Input,
    input_index: usize,
    resampler: filter::Graph,
    encoder: encoder::Audio,
    decoder: decoder::Audio,
    is_eof: bool,
    stats: Stats,
    output_time_base: (i32, i32),
    input_time_base: (i32, i32),
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

        let (input_index, decoder, stream) = setup_audio_decoder(&mut input)?;
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

        let stats = Stats {
            first_decoded_packet_pts: None,
            last_decoded_packet_pts: None,
            last_decoded_packet_duration: None,
            first_encoded_packet_pts: None,
            last_encoded_packet_pts: None,
            last_encoded_packet_duration: None,
            decoded_packets_number: 0,
            encoded_packets_number: 0,
        };

        let input_time_base = (stream.time_base().0, stream.time_base().1);
        let output_time_base = (1, encoder.rate() as i32);

        Ok(Self {
            input,
            input_index,
            decoder,
            resampler,
            encoder,
            stats,
            is_eof: false,
            input_time_base,
            output_time_base,
        })
    }

    pub fn stats(&self) -> &Stats {
        &self.stats
    }

    pub fn receive_next_transcoded_packets(
        &mut self,
    ) -> Result<Option<Vec<utils::Packet>>, TranscodeError> {
        match self.get_packet_from_input() {
            Some(packet) => {
                trace!("Send 1 packet to decoder");
                self.send_packet_to_decoder(&packet)?;
                self.update_stats_for_input_packet(&packet);

                let encoded_packets = self.receive_and_process_decoded_frames()?;
                trace!("Read encoded packets: {}", encoded_packets.len());

                for pkt in &encoded_packets {
                    self.update_stats_for_encoded_packet(pkt);
                }

                let prepared_packets: Vec<_> = encoded_packets
                    .into_iter()
                    .map(|pkt| self.prepare_packet(pkt))
                    .collect();

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

                for pkt in &final_encoded_packets {
                    self.update_stats_for_encoded_packet(pkt);
                }

                let prepared_packets: Vec<_> = final_encoded_packets
                    .into_iter()
                    .map(|pkt| self.prepare_packet(pkt))
                    .collect();

                self.is_eof = true;

                Ok(Some(prepared_packets))
            }
        }
    }

    fn prepare_packet(&self, pkt: Packet) -> utils::Packet {
        let pts = pkt.pts().unwrap_or_default();
        let duration = pkt.duration();
        let output_time_base = (1, self.encoder.rate() as i32);
        let data = pkt.data().unwrap_or_default().to_vec();

        utils::Packet::new(
            Timestamp::new(pts, output_time_base),
            Timestamp::new(duration, output_time_base),
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

    fn update_stats_for_input_packet(&mut self, packet: &Packet) {
        self.stats.decoded_packets_number += 1;

        let pts_timestamp = packet
            .pts()
            .map(|pts| Timestamp::new(pts, self.input_time_base));
        let dur_timestamp = Timestamp::new(packet.duration(), self.input_time_base);

        if self.stats.first_decoded_packet_pts.is_none() {
            self.stats.first_decoded_packet_pts = pts_timestamp.clone();
        }

        self.stats.last_decoded_packet_pts = pts_timestamp;
        self.stats.last_decoded_packet_duration = Some(dur_timestamp);
    }

    fn update_stats_for_encoded_packet(&mut self, packet: &Packet) {
        self.stats.encoded_packets_number += 1;

        let pts_timestamp = packet
            .pts()
            .map(|pts| Timestamp::new(pts, self.output_time_base));
        let dur_timestamp = Timestamp::new(packet.duration(), self.output_time_base);

        if self.stats.first_encoded_packet_pts.is_none() {
            self.stats.first_encoded_packet_pts = pts_timestamp.clone();
        }

        self.stats.last_encoded_packet_pts = pts_timestamp;
        self.stats.last_encoded_packet_duration = Some(dur_timestamp);
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
