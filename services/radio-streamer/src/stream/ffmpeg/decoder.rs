use crate::stream::constants::AUDIO_SAMPLING_FREQUENCY;
use crate::stream::types::Buffer;
use crate::unwrap_or_return;
use actix_web::web::Bytes;
use ffmpeg_next::codec::Id::PCM_F16LE;
use ffmpeg_next::ffi::AVSampleFormat::AV_SAMPLE_FMT_S16;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::Sample;
use ffmpeg_next::option::Type::SampleFormat;
use ffmpeg_next::{frame, rescale, ChannelLayout, Rescale};
use std::error::Error;
use std::sync::mpsc::{channel, Receiver};
use std::time::Duration;

#[derive(Debug, thiserror::Error)]
pub(crate) enum AudioFileDecodeError {
    #[error("Unable to open input file")]
    OpenFile,
    #[error("Unable to find audio stream")]
    OpenAudioStream,
    #[error("Unable to seek in input file")]
    Seek,
}

impl Into<Buffer> for frame::Audio {
    fn into(self) -> Buffer {
        Buffer::new(
            Bytes::copy_from_slice(&self.data(0)),
            Duration::from_millis(self.pts().unwrap_or_default() as u64),
            Duration::from_millis(self.pts().unwrap_or_default() as u64),
        )
    }
}

pub(crate) fn decode_audio_file(
    source_url: &str,
    offset: &Duration,
) -> Result<Receiver<frame::Audio>, AudioFileDecodeError> {
    let (frame_sender, frame_receiver) = channel();

    let mut ictx =
        ffmpeg_next::format::input(&source_url.to_string()).expect("Unable to open file");

    let time_base = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .expect("Unable to get audio stream")
        .time_base();

    {
        let position_millis = (offset.as_millis() as i64).rescale(time_base, rescale::TIME_BASE);
        ictx.seek(position_millis, ..position_millis)
            .expect("Unable to seek");
    };

    let input_stream = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .expect("Unable to get audio stream");

    let mut decoder = input_stream
        .codec()
        .decoder()
        .audio()
        .expect("Unable to get audio decoder");

    decoder.set_parameters(input_stream.parameters()).unwrap();

    if decoder.channel_layout().is_empty() {
        decoder.set_channel_layout(ChannelLayout::default(decoder.channels() as i32));
    }

    let mut resampler = decoder
        .resampler(
            Sample::I16(Type::Packed),
            ChannelLayout::STEREO,
            AUDIO_SAMPLING_FREQUENCY as u32,
        )
        .expect("Unable to initialize resampler");

    std::thread::spawn(move || {
        for (_, packet) in ictx.packets() {
            unwrap_or_return!(decoder.send_packet(&packet));
            let mut decoded = frame::Audio::empty();
            while decoder.receive_frame(&mut decoded).is_ok() {
                let mut resampled = frame::Audio::empty();
                resampled.clone_from(&decoded);
                unwrap_or_return!(resampler.run(&decoded, &mut resampled));
                unwrap_or_return!(frame_sender.send(resampled));
            }
        }

        unwrap_or_return!(decoder.send_eof());
        let mut decoded = frame::Audio::empty();
        while decoder.receive_frame(&mut decoded).is_ok() {
            let mut resampled = frame::Audio::empty();
            resampled.clone_from(&decoded);
            unwrap_or_return!(resampler.run(&decoded, &mut resampled));
            unwrap_or_return!(frame_sender.send(resampled));
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
        while let Ok(frame) = decoded_frames.recv() {
            assert!(frame.pts().is_some());
            frames_count += 1;
        }

        assert_eq!(123, frames_count);
    }
}
