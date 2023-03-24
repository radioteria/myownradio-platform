use crate::stream::constants::{AUDIO_SAMPLING_FREQUENCY, INTERNAL_TIME_BASE};
use crate::stream::ffmpeg::utils::convert_sample_to_byte_data;
use crate::stream::ffmpeg::INTERNAL_CHANNEL_LAYOUT;
use crate::stream::types::Buffer;
use crate::unwrap_or_return;
use actix_web::web::Bytes;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::Sample;
use ffmpeg_next::option::Type::SampleFormat;
use ffmpeg_next::{frame, rescale, ChannelLayout, Rescale};
use std::error::Error;
use std::io::Write;
use std::sync::mpsc::{sync_channel, Receiver, SyncSender};
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
        let pts = self.pts().unwrap_or_default() as u64;
        let data_len = self.plane::<(i16, i16)>(0).len() * 4;

        Buffer::new(
            Bytes::copy_from_slice(&self.data(0)[..data_len]),
            Duration::from_millis(pts),
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

    let time_base = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| AudioFileDecodeError::NoAudioStream)?
        .time_base();

    {
        // let position_millis = (offset.as_millis() as i64).rescale((1, 1000), time_base);
        // ictx.seek(position_millis, ..position_millis)
        //     .map_err(|error| AudioFileDecodeError::Seek(error))?;
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
            AUDIO_SAMPLING_FREQUENCY as u32,
        )
        .map_err(|error| AudioFileDecodeError::AudioDecoder(error))?;

    std::thread::spawn(move || {
        for (_, mut packet) in ictx.packets() {
            packet.rescale_ts(time_base, INTERNAL_TIME_BASE);

            unwrap_or_return!(decoder.send_packet(&packet));
            let mut decoded = frame::Audio::empty();
            while decoder.receive_frame(&mut decoded).is_ok() {
                let mut resampled = frame::Audio::empty();
                resampled.clone_from(&decoded);
                unwrap_or_return!(resampler.run(&decoded, &mut resampled));
                unwrap_or_return!(frame_sender.send(resampled.into()));
            }
        }

        unwrap_or_return!(decoder.send_eof());
        let mut decoded = frame::Audio::empty();
        while decoder.receive_frame(&mut decoded).is_ok() {
            let mut resampled = frame::Audio::empty();
            resampled.clone_from(&decoded);
            unwrap_or_return!(resampler.run(&decoded, &mut resampled));
            unwrap_or_return!(frame_sender.send(resampled.into()));
        }
    });

    Ok(frame_receiver)
}

#[cfg(test)]
mod tests {
    use crate::stream::types::Buffer;
    use std::time::Duration;

    #[test]
    fn test_decode_audio_file() {
        let decoded_frames = super::decode_audio_file(
            "tests/fixtures/decoder_test_file.wav",
            &Duration::from_secs(0),
        )
        .unwrap();

        let mut frames_count = 0;
        let mut max_pts = Duration::from_secs(0);
        while let Ok(frame) = decoded_frames.recv() {
            frames_count += 1;
            max_pts = frame.pts_hint().clone();
        }

        assert_eq!(123, frames_count);
        assert_eq!(Duration::from_secs_f32(2.834286), max_pts);
    }
}
