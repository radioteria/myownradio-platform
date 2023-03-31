use crate::stream::constants::INTERNAL_TIME_BASE;
use crate::stream::ffmpeg::{
    INTERNAL_CHANNEL_LAYOUT, INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_RATE, RESAMPLER_TIME_BASE,
};
use crate::stream::types::{Buffer, SharedFrame};
use actix_rt::Runtime;
use actix_web::web::Bytes;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::Sample;
use ffmpeg_next::frame::Audio;
use ffmpeg_next::{frame, rescale, ChannelLayout, Packet, Rational, Rescale, Stream};
use futures::channel::mpsc::{channel, Receiver, SendError, Sender};
use futures::SinkExt;
use std::any::Any;
use std::time::Duration;

#[derive(Debug, thiserror::Error)]
pub(crate) enum AudioFileDecodeError {
    #[error("Unable to open input file: {0}")]
    OpenFile(ffmpeg_next::Error),
    #[error("Unable to find audio stream")]
    NoAudioStream,
    #[error("Unable to seek in input file: {0}")]
    Seek(ffmpeg_next::Error),
    #[error("Unable to initialize audio decoder: {0}")]
    AudioDecoder(ffmpeg_next::Error),
    #[error("Unable to initialize resampler: {0}")]
    Resampler(ffmpeg_next::Error),
}

impl Into<Buffer> for frame::Audio {
    fn into(self) -> Buffer {
        let pts = self
            .pts()
            .unwrap_or_default()
            .rescale(RESAMPLER_TIME_BASE, INTERNAL_TIME_BASE) as u64;
        let data_len = self.samples() * 4; // number of bytes required to represent each pair of 16-bit integers as four 8-bit integers

        Buffer::new(
            Bytes::copy_from_slice(&self.data(0)[..data_len]),
            Duration::from_millis(pts),
        )
    }
}

impl Into<SharedFrame> for ffmpeg_next::frame::Audio {
    fn into(self) -> SharedFrame {
        let millis = self
            .pts()
            .unwrap_or_default()
            .rescale(RESAMPLER_TIME_BASE, INTERNAL_TIME_BASE) as u64;
        let duration =
            (self.samples() as i64).rescale(RESAMPLER_TIME_BASE, INTERNAL_TIME_BASE) as u64;

        let data_len = self.samples() * INTERNAL_SAMPLE_SIZE;
        let data = &self.data(0)[..data_len];

        SharedFrame::new(
            Duration::from_millis(millis),
            Duration::from_millis(duration),
            Vec::from(data),
        )
    }
}

pub(crate) fn decode_audio_file(
    source_url: &str,
    offset: &Duration,
) -> Result<Receiver<SharedFrame>, AudioFileDecodeError> {
    let (mut frame_sender, frame_receiver) = channel(0);

    let mut ictx = ffmpeg_next::format::input(&source_url.to_string())
        .map_err(|error| AudioFileDecodeError::OpenFile(error))?;

    let input_time_base = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| AudioFileDecodeError::NoAudioStream)?
        .time_base();

    {
        let position = (offset.as_millis() as i64).rescale(INTERNAL_TIME_BASE, rescale::TIME_BASE);
        ictx.seek(position, ..position)
            .map_err(|error| AudioFileDecodeError::Seek(error))?;
    };

    let input_stream = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| AudioFileDecodeError::NoAudioStream)?;
    let input_stream_number = input_stream.id() as usize;

    let mut decoder = input_stream
        .codec()
        .decoder()
        .audio()
        .map_err(|error| AudioFileDecodeError::AudioDecoder(error))?;

    decoder
        .set_parameters(input_stream.parameters())
        .map_err(|error| AudioFileDecodeError::AudioDecoder(error))?;

    if decoder.channel_layout().is_empty() {
        decoder.set_channel_layout(ChannelLayout::default(decoder.channels() as i32));
    }

    let mut resampler = decoder
        .resampler(
            Sample::I16(Type::Packed),
            INTERNAL_CHANNEL_LAYOUT,
            INTERNAL_SAMPLING_RATE as u32,
        )
        .map_err(|error| AudioFileDecodeError::AudioDecoder(error))?;

    std::thread::spawn(move || {
        let audio_packets = ictx
            .packets()
            .filter(|(_, packet)| packet.stream() == input_stream_number)
            .map(|(s, packet)| packet);

        if let Err(error) = process_audio_stream_packets(
            audio_packets,
            &input_time_base,
            &mut decoder,
            &mut resampler,
            &mut frame_sender,
        ) {
            eprintln!("ERROR!: {:?}", error);
        }
    });

    Ok(frame_receiver)
}

#[derive(thiserror::Error, Debug)]
enum ProcessAudioStreamPacketsError {
    #[error(transparent)]
    FFmpegError(#[from] ffmpeg_next::Error),
    #[error(transparent)]
    SendError(#[from] SendError),
}

fn process_audio_stream_packets<I>(
    mut packets: I,
    input_time_base: &Rational,
    decoder: &mut ffmpeg_next::decoder::Audio,
    resampler: &mut ffmpeg_next::software::resampling::Context,
    frame_sender: &mut Sender<SharedFrame>,
) -> Result<(), ProcessAudioStreamPacketsError>
where
    I: Iterator<Item = Packet>,
{
    let runtime = Runtime::new().expect("Unable to init async runtime");

    for packet in packets {
        decoder.send_packet(&packet)?;

        let frames = receive_and_process_decoded_frames(input_time_base, decoder, resampler)?;
        for frame in frames {
            runtime.block_on(async { frame_sender.send(frame.into()).await })?;
        }
    }

    decoder.send_eof()?;

    let frames = receive_and_process_decoded_frames(input_time_base, decoder, resampler)?;
    for frame in frames {
        runtime.block_on(async { frame_sender.send(frame.into()).await })?;
    }

    Ok(())
}

fn receive_and_process_decoded_frames(
    input_time_base: &Rational,
    decoder: &mut ffmpeg_next::decoder::Audio,
    resampler: &mut ffmpeg_next::software::resampling::Context,
) -> Result<Vec<Audio>, ffmpeg_next::Error> {
    let decoder_time_base = decoder.time_base();

    let mut frames = vec![];

    let mut decoded_frame = ffmpeg_next::frame::Audio::empty();
    while decoder.receive_frame(&mut decoded_frame).is_ok() {
        let rescaled_ts = decoded_frame
            .pts()
            .map(|pts| pts.rescale(input_time_base.clone(), decoder_time_base));
        decoded_frame.set_pts(rescaled_ts);

        let mut resampled_frame = ffmpeg_next::frame::Audio::empty();
        resampled_frame.clone_from(&decoded_frame);
        resampler.run(&decoded_frame, &mut resampled_frame)?;
        let rescaled_ts = resampled_frame
            .pts()
            .map(|pts| pts.rescale(decoder_time_base, RESAMPLER_TIME_BASE));
        resampled_frame.set_pts(rescaled_ts);
        frames.push(resampled_frame);
    }

    Ok(frames)
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
                Duration::from_millis(2836),
                Duration::from_millis(0),
            ),
            (
                "tests/fixtures/test_file.ogg",
                Duration::from_millis(2836),
                Duration::from_millis(1500),
            ),
        ];

        for (filename, expected_duration, offset) in test_files {
            eprintln!("file: {}", filename);

            let mut frames =
                super::decode_audio_file(filename, &offset).expect("Unable to decode file");

            let mut duration = Duration::default();

            while let Some(frame) = frames.next().await {
                duration = (*frame.duration() + *frame.pts());
            }

            assert_eq!(expected_duration, duration);
        }
    }
}
