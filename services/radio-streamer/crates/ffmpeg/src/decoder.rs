use crate::utils::{rescale_audio_frame_ts, Frame, Timestamp};
use crate::{INTERNAL_CHANNELS_NUMBER, INTERNAL_TIME_BASE};
use crate::{INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_RATE, RESAMPLER_TIME_BASE};
use ffmpeg_next::codec::Context;
use ffmpeg_next::format::context::Input;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::Sample;
use ffmpeg_next::frame::Audio;
use ffmpeg_next::{rescale, ChannelLayout, Packet, Rational, Rescale};
use futures::channel::mpsc::{channel, Receiver, SendError, Sender};
use futures::SinkExt;
use std::time::Duration;

struct AudioDecoder {
    input_index: usize,
    input_time_base: Rational,
    decoder: ffmpeg_next::decoder::Audio,
    resampler: ffmpeg_next::software::resampling::Context,
    async_runtime: actix_rt::Runtime,
    async_sender: Sender<Frame>,
}

#[derive(Debug, thiserror::Error)]
pub enum AudioDecoderError {
    #[error("Unable to open input file: {0}")]
    OpenFileError(ffmpeg_next::Error),
    #[error("Audio stream not found")]
    AudioStreamNotFound,
    #[error("Audio decoding failed: {0}")]
    AudioDecoderError(ffmpeg_next::Error),
    #[error("Audio resampling failed: {0}")]
    ResamplingError(ffmpeg_next::Error),
    #[error("Unable to seek input to specified position")]
    SeekError(ffmpeg_next::Error),
    #[error("Unable to send processed frame to Sender")]
    SendError(SendError),
}

impl AudioDecoder {
    fn resample_and_process_frames(&mut self, decoded: &Audio) -> Result<(), AudioDecoderError> {
        let mut delay = None;

        loop {
            let mut resampled = Audio::empty();
            resampled.clone_from(decoded);

            delay = match delay {
                Some(_) => self
                    .resampler
                    .flush(&mut resampled)
                    .map_err(|error| AudioDecoderError::ResamplingError(error))?,
                None => self
                    .resampler
                    .run(decoded, &mut resampled)
                    .map_err(|error| AudioDecoderError::ResamplingError(error))?,
            };

            rescale_audio_frame_ts(
                &mut resampled,
                self.decoder.time_base(),
                RESAMPLER_TIME_BASE.into(),
            );

            self.async_runtime
                .block_on(self.async_sender.send(resampled.into()))
                .map_err(|error| AudioDecoderError::SendError(error))?;

            if delay.is_none() {
                break;
            }
        }

        Ok(())
    }

    fn send_packet_to_decoder(&mut self, packet: &Packet) -> Result<(), AudioDecoderError> {
        self.decoder
            .send_packet(packet)
            .map_err(|error| AudioDecoderError::AudioDecoderError(error))
    }

    fn send_eof_to_decoder(&mut self) -> Result<(), AudioDecoderError> {
        self.decoder
            .send_eof()
            .map_err(|error| AudioDecoderError::AudioDecoderError(error))
    }

    fn receive_and_process_decoded_frames(&mut self) -> Result<(), AudioDecoderError> {
        let mut decoded = Audio::empty();
        while self.decoder.receive_frame(&mut decoded).is_ok() {
            let timestamp = decoded.timestamp();
            decoded.set_pts(timestamp);

            self.resample_and_process_frames(&decoded)?;
        }

        Ok(())
    }
}

fn make_audio_decoder(
    ictx: &mut Input,
    async_runtime: actix_rt::Runtime,
    async_sender: Sender<Frame>,
) -> Result<AudioDecoder, AudioDecoderError> {
    let input = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| AudioDecoderError::AudioStreamNotFound)?;
    let input_index = input.index();
    let input_time_base = input.time_base();
    let context = Context::from_parameters(input.parameters())
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    let mut decoder = context
        .decoder()
        .audio()
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    decoder
        .set_parameters(input.parameters())
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    if decoder.channel_layout().is_empty() {
        decoder.set_channel_layout(ChannelLayout::default(decoder.channels() as i32));
    }

    let resampler = decoder
        .resampler(
            Sample::I16(Type::Packed),
            ChannelLayout::default(INTERNAL_CHANNELS_NUMBER),
            INTERNAL_SAMPLING_RATE as u32,
        )
        .map_err(|error| AudioDecoderError::ResamplingError(error))?;

    Ok(AudioDecoder {
        input_index,
        input_time_base,
        decoder,
        resampler,
        async_runtime,
        async_sender,
    })
}

impl Into<Frame> for Audio {
    fn into(self) -> Frame {
        let pts = Timestamp::new(self.pts().unwrap_or_default(), RESAMPLER_TIME_BASE);
        let duration = Timestamp::new(self.samples() as i64, RESAMPLER_TIME_BASE);

        let data_len = self.samples() * INTERNAL_SAMPLE_SIZE;
        let data = &self.data(0)[..data_len];

        Frame::new(pts, duration, Vec::from(data))
    }
}

pub fn decode_audio_file(
    source_url: &str,
    offset: &Duration,
) -> Result<Receiver<Frame>, AudioDecoderError> {
    let (frame_sender, frame_receiver) = channel(0);

    let mut ictx = ffmpeg_next::format::input(&source_url.to_string())
        .map_err(|error| AudioDecoderError::OpenFileError(error))?;

    if !offset.is_zero() {
        let position_millis = offset.as_millis() as i64;
        let position = position_millis.rescale(INTERNAL_TIME_BASE, rescale::TIME_BASE);

        ictx.seek(position, ..position)
            .map_err(|error| AudioDecoderError::SeekError(error))?;
    };

    std::thread::spawn(move || {
        let async_runtime = actix_rt::Runtime::new().expect("Unable to initialize async runtime");
        let mut audio_decoder = make_audio_decoder(&mut ictx, async_runtime, frame_sender)
            .expect("Unable to initialize audio decoder");

        for (stream, mut packet) in ictx.packets() {
            if stream.index() == audio_decoder.input_index {
                packet.rescale_ts(stream.time_base(), audio_decoder.decoder.time_base());
                audio_decoder.send_packet_to_decoder(&packet).unwrap();
                audio_decoder.receive_and_process_decoded_frames().unwrap();
            }
        }

        audio_decoder.send_eof_to_decoder().unwrap();
        audio_decoder.receive_and_process_decoded_frames().unwrap();
    });

    Ok(frame_receiver)
}

#[cfg(test)]
mod tests {
    use futures::StreamExt;
    use std::time::Duration;

    #[actix_rt::test]
    async fn test_decoding_test_files() {
        let test_files = vec![
            (
                "tests/fixtures/test_file.wav",
                Duration::from_millis(2834),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.wav",
                Duration::from_millis(2834),
                Duration::from_millis(1500),
            ),
            (
                "tests/fixtures/test_file.aac",
                Duration::from_millis(2877),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.aac",
                Duration::from_millis(2877),
                Duration::from_millis(1500),
            ),
            (
                "tests/fixtures/test_file.flac",
                Duration::from_millis(2833),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.flac",
                Duration::from_millis(2833),
                Duration::from_millis(1500),
            ),
            (
                "tests/fixtures/test_file.m4a",
                Duration::from_millis(2854),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.m4a",
                Duration::from_millis(2854),
                Duration::from_millis(1500),
            ),
            (
                "tests/fixtures/test_file.mp3",
                Duration::from_millis(2858),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.mp3",
                Duration::from_millis(2858),
                Duration::from_millis(1500),
            ),
            (
                "tests/fixtures/test_file.ogg",
                Duration::from_millis(2834),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.ogg",
                Duration::from_millis(2834),
                Duration::from_millis(1500),
            ),
        ];

        for (filename, expected_duration, offset) in test_files {
            eprintln!("file: {}", filename);

            let mut frames =
                super::decode_audio_file(filename, &offset).expect("Unable to decode file");

            let mut duration = Duration::default();

            while let Some(frame) = frames.next().await {
                duration = frame.duration().into();
                duration += frame.pts().into();
            }

            assert_eq!(expected_duration, duration);
        }
    }

    #[actix_rt::test]
    async fn test_decoding_file_by_url() {
        let test_file_url = "https://download.samplelib.com/mp3/sample-6s.mp3";
        let mut frames = super::decode_audio_file(test_file_url, &Duration::from_secs(0))
            .expect("Unable to decode file");

        let mut duration = Duration::default();

        while let Some(frame) = frames.next().await {
            duration = frame.duration().into();
            duration += frame.pts().into();
        }

        assert_eq!(Duration::from_millis(6189), duration);
    }

    #[actix_rt::test]
    async fn test_seek_accuracy() {
        let test_file_path = "tests/fixtures/test_file.wav";
        let seek_position = Duration::from_millis(400);

        let frame = super::decode_audio_file(test_file_path, &seek_position)
            .expect("Unable to decode file")
            .next()
            .await
            .unwrap();

        assert_eq!(seek_position, frame.pts().into());
    }
}
