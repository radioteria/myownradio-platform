use crate::ffmpeg::setup_resampling_filter;
use crate::utils::{Frame, Timestamp};
use crate::INTERNAL_TIME_BASE;
use crate::{INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_FREQUENCY, RESAMPLER_TIME_BASE};
use ffmpeg_next::codec::Context;
use ffmpeg_next::format::context::Input;
use ffmpeg_next::frame::Audio;
use ffmpeg_next::{rescale, ChannelLayout, Packet, Rescale};
use futures::channel::mpsc::{channel, Receiver, SendError};
use futures::SinkExt;
use std::time::Duration;
use tracing::{debug, error, trace, warn};

struct AudioDecoder {
    input_index: usize,
    decoder: ffmpeg_next::decoder::Audio,
    resampling_filter: ffmpeg_next::filter::Graph,
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
    #[tracing::instrument(skip(self))]
    fn send_frame_to_resampler(&mut self, frame: &Audio) -> Result<(), AudioDecoderError> {
        self.resampling_filter
            .get("in")
            .expect("Unable to get 'in' pad on filter")
            .source()
            .add(frame)
            .map_err(|error| AudioDecoderError::ResamplingError(error))?;

        Ok(())
    }

    #[tracing::instrument(skip(self))]
    fn receive_resampled_frames(&mut self) -> Result<Vec<Frame>, AudioDecoderError> {
        let mut frames = vec![];

        let mut resampled = Audio::empty();
        while self
            .resampling_filter
            .get("out")
            .expect("Unable to get 'out' pad on filter")
            .sink()
            .samples(&mut resampled, 1024)
            .is_ok()
        {
            frames.push(resampled.clone().into());
        }

        Ok(frames)
    }

    #[tracing::instrument(skip(self, packet))]
    fn send_packet_to_decoder(&mut self, packet: &Packet) -> Result<(), AudioDecoderError> {
        self.decoder
            .send_packet(packet)
            .map_err(|error| AudioDecoderError::AudioDecoderError(error))
    }

    #[tracing::instrument(skip(self))]
    fn send_eof_to_decoder(&mut self) -> Result<(), AudioDecoderError> {
        self.decoder
            .send_eof()
            .map_err(|error| AudioDecoderError::AudioDecoderError(error))
    }

    #[tracing::instrument(skip(self))]
    fn receive_and_process_decoded_frames(&mut self) -> Result<Vec<Frame>, AudioDecoderError> {
        let mut frames = vec![];

        let mut ff_frame = Audio::empty();
        while self.decoder.receive_frame(&mut ff_frame).is_ok() {
            let timestamp = ff_frame.timestamp();
            ff_frame.set_pts(timestamp);
            self.send_frame_to_resampler(&ff_frame)?;
            frames.append(&mut self.receive_resampled_frames()?);
        }

        Ok(frames)
    }
}

fn make_audio_decoder(ictx: &mut Input) -> Result<AudioDecoder, AudioDecoderError> {
    let input = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| AudioDecoderError::AudioStreamNotFound)?;
    let input_index = input.index();
    let context = Context::from_parameters(input.parameters())
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    debug!("Initializing decoder");
    let mut decoder = context
        .decoder()
        .audio()
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    decoder
        .set_parameters(input.parameters())
        .map_err(|error| AudioDecoderError::AudioDecoderError(error))?;

    if decoder.channel_layout().is_empty() {
        debug!("Setting channel layout");
        decoder.set_channel_layout(ChannelLayout::default(decoder.channels() as i32));
    }

    let input_rate = decoder.rate();
    let output_rate = INTERNAL_SAMPLING_FREQUENCY as u32;

    debug!(input_rate, output_rate, "Initializing resampler");
    let resampling_filter = setup_resampling_filter(INTERNAL_SAMPLING_FREQUENCY as u32, &decoder)
        .map_err(|error| AudioDecoderError::ResamplingError(error))?;

    Ok(AudioDecoder {
        input_index,
        decoder,
        resampling_filter,
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

#[derive(Debug)]
pub enum DecoderMessage {
    Frame(Frame),
    EOF,
}

#[tracing::instrument]
pub fn decode_audio_file(
    source_url: &str,
    offset: &Duration,
) -> Result<Receiver<DecoderMessage>, AudioDecoderError> {
    let (frame_sender, frame_receiver) = channel(0);

    debug!(source_url, "Open source file");

    let mut ictx = ffmpeg_next::format::input(&source_url.to_string())
        .map_err(|error| AudioDecoderError::OpenFileError(error))?;

    if !offset.is_zero() {
        let position_millis = offset.as_millis() as i64;
        let position = position_millis.rescale(INTERNAL_TIME_BASE, rescale::TIME_BASE);

        debug!(?offset, "Seek to offset");

        ictx.seek(position, ..position)
            .map_err(|error| AudioDecoderError::SeekError(error))?;
    };

    std::thread::spawn(move || {
        let async_runtime = actix_rt::Runtime::new().expect("Unable to initialize async runtime");
        let mut audio_decoder =
            make_audio_decoder(&mut ictx).expect("Unable to initialize audio decoder");
        let mut frame_sender = frame_sender;

        debug!("Starting reading packets");

        for (stream, mut packet) in ictx.packets() {
            if stream.index() == audio_decoder.input_index {
                packet.rescale_ts(stream.time_base(), audio_decoder.decoder.time_base());

                trace!("Sending packet to decoder");

                if let Err(error) = audio_decoder.send_packet_to_decoder(&packet) {
                    error!(?error, "Unable to send packet to decoder");
                    return;
                }

                let frames = match audio_decoder.receive_and_process_decoded_frames() {
                    Ok(frames) => frames,
                    Err(error) => {
                        error!(?error, "Unable to receive and process decoded frames");
                        return;
                    }
                };

                for frame in frames {
                    if async_runtime
                        .block_on(frame_sender.send(DecoderMessage::Frame(frame)))
                        .is_err()
                    {
                        return;
                    };
                }
            }
        }

        trace!("Sending EOF to decoder");

        if let Err(error) = audio_decoder.send_eof_to_decoder() {
            error!(?error, "Unable to send EOF to decoder");
            return;
        };

        trace!("Processing last decoded frames");

        let frames = match audio_decoder.receive_and_process_decoded_frames() {
            Ok(frames) => frames,
            Err(error) => {
                error!(?error, "Unable to receive and process final decoded frames");
                return;
            }
        };

        for frame in frames {
            if async_runtime
                .block_on(frame_sender.send(DecoderMessage::Frame(frame)))
                .is_err()
            {
                return;
            };
        }

        let _ = async_runtime.block_on(frame_sender.send(DecoderMessage::EOF));
    });

    Ok(frame_receiver)
}

#[cfg(test)]
mod tests {
    use crate::decoder::DecoderMessage;
    use ffmpeg_next::format::input;
    use ffmpeg_next::{rescale, Rescale};
    use futures::StreamExt;
    use std::time::Duration;
    use tracing_test::traced_test;

    #[ctor::ctor]
    fn init() {
        ffmpeg_next::init().expect("Unable to initialize ffmpeg");
        // ffmpeg_next::log::set_level(ffmpeg_next::log::Level::Trace);
    }

    const TEST_FILES: [(&str, Duration, Duration); 13] = [
        (
            "tests/fixtures/test_file.wav",
            Duration::from_millis(10216),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.wav",
            Duration::from_millis(10216),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/test_file.aac",
            Duration::from_millis(10262),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.aac",
            Duration::from_millis(10262),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/test_file.flac",
            Duration::from_millis(10216),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.flac",
            Duration::from_millis(10216),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/test_file.m4a",
            Duration::from_millis(10216),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.m4a",
            Duration::from_millis(10216),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/test_file.mp3",
            Duration::from_millis(10216),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.mp3",
            Duration::from_millis(10216),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/test_file.ogg",
            Duration::from_millis(10216),
            Duration::from_millis(0),
        ),
        (
            "tests/fixtures/test_file.ogg",
            Duration::from_millis(10216),
            Duration::from_millis(1500),
        ),
        (
            "tests/fixtures/sample-6s.mp3",
            /* [FORMAT]duration=6.426122[/FORMAT] */
            Duration::from_millis(6416),
            Duration::from_millis(1500),
        ),
    ];

    #[actix_rt::test]
    async fn test_opening_source_files() {
        for (filename, ..) in TEST_FILES {
            assert!(input(&filename).is_ok());
        }
    }

    #[actix_rt::test]
    async fn test_seeking_source_files() {
        let position = 1500i64.rescale(crate::INTERNAL_TIME_BASE, rescale::TIME_BASE);
        for (filename, ..) in TEST_FILES {
            assert!(input(&filename)
                .expect("Unable to open input file")
                .seek(position, ..position)
                .is_ok());
        }
    }

    #[actix_rt::test]
    async fn test_iterating_over_source_packets() {
        for (filename, ..) in TEST_FILES {
            let mut ictx = input(&filename).expect("Unable to open input file");

            assert!(ictx.packets().last().is_some());
        }
    }

    #[actix_rt::test]
    async fn test_decoding_test_files() {
        for (filename, expected_duration, offset) in TEST_FILES {
            eprintln!("file: {}", filename);

            let mut frames =
                super::decode_audio_file(filename, &offset).expect("Unable to decode file");

            let mut duration = Duration::default();

            while let Some(DecoderMessage::Frame(frame)) = frames.next().await {
                duration = frame.duration().into();
                duration += frame.pts().into();
            }

            assert_eq!(expected_duration.as_millis(), duration.as_millis());
        }
    }

    #[actix_rt::test]
    #[traced_test]
    async fn test_decoding_file_by_url() {
        let test_file_url = "https://download.samplelib.com/mp3/sample-6s.mp3";
        let mut frames = super::decode_audio_file(test_file_url, &Duration::from_secs(0))
            .expect("Unable to decode file");

        let mut duration = Duration::default();

        while let Some(DecoderMessage::Frame(frame)) = frames.next().await {
            duration = frame.duration().into();
            duration += frame.pts().into();
        }

        assert_eq!(Duration::from_millis(6416), duration);
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

        match frame {
            DecoderMessage::Frame(frame) => {
                assert_eq!(seek_position, frame.pts().into());
            }
            msg => panic!("Unexpected first decoder message: {:?}", msg),
        }
    }
}
