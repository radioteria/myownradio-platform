use crate::stream::constants::{AUDIO_SAMPLING_FREQUENCY, INTERNAL_TIME_BASE};
use crate::stream::ffmpeg::{INTERNAL_CHANNEL_LAYOUT, INTERNAL_SAMPLING_RATE, RESAMPLER_TIME_BASE};
use crate::stream::types::Buffer;
use crate::unwrap_or_return;
use actix_web::web::Bytes;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::{input, Sample};
use ffmpeg_next::frame::Audio;
use ffmpeg_next::{frame, rescale, ChannelLayout, Rational, Rescale};
use std::io::Write;
use std::sync::mpsc::{sync_channel, Receiver, SendError};
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

pub(crate) fn decode_audio_file(
    source_url: &str,
    offset: &Duration,
) -> Result<Receiver<Buffer>, AudioFileDecodeError> {
    let (frame_sender, frame_receiver) = sync_channel(0);

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
        let packets = ictx.packets();
        if let Err(error) = process_audio_stream_packets(
            packets,
            &input_time_base,
            &mut decoder,
            &mut resampler,
            &frame_sender,
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
    SendError(#[from] SendError<Buffer>),
}

fn process_audio_stream_packets(
    mut packets: ffmpeg_next::format::context::input::PacketIter,
    input_time_base: &Rational,
    decoder: &mut ffmpeg_next::decoder::Audio,
    resampler: &mut ffmpeg_next::software::resampling::Context,
    frame_sender: &std::sync::mpsc::SyncSender<Buffer>,
) -> Result<(), ProcessAudioStreamPacketsError> {
    for (_, mut packet) in packets {
        decoder.send_packet(&packet)?;

        let frames = receive_and_process_decoded_frames(input_time_base, decoder, resampler)?;
        for frame in frames {
            frame_sender.send(frame.into())?;
        }
    }

    decoder.send_eof()?;

    let frames = receive_and_process_decoded_frames(input_time_base, decoder, resampler)?;
    for frame in frames {
        frame_sender.send(frame.into())?;
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
        frames.push(resampled_frame);
    }

    Ok(frames)
}

#[cfg(test)]
mod tests {
    use crate::stream::types::Buffer;
    use std::time::Duration;

    #[test]
    fn test_decode_audio_file() {
        let decoded_frames = super::decode_audio_file(
            "tests/fixtures/decoder_test_file.wav",
            &Duration::from_millis(1200),
        )
        .unwrap();

        let mut frames_count = 0;
        let mut max_pts = Duration::from_secs(0);
        let mut min_pts = Duration::from_secs(u64::MAX);

        while let Ok(frame) = decoded_frames.recv() {
            frames_count += 1;
            max_pts = frame.pts_hint().clone();
            min_pts = max_pts.min(min_pts);
        }

        assert_eq!(71, frames_count);
        assert_eq!(2596, max_pts.as_millis());
        assert_eq!(1103, min_pts.as_millis());
    }
}
