use crate::stream::constants::AUDIO_SAMPLING_FREQUENCY;
use crate::unwrap_or_return;
use ffmpeg_next::codec::Id::PCM_F16LE;
use ffmpeg_next::ffi::AVSampleFormat::AV_SAMPLE_FMT_S16;
use ffmpeg_next::format::Sample;
use ffmpeg_next::option::Type::SampleFormat;
use ffmpeg_next::{frame, ChannelLayout};
use std::sync::mpsc::{channel, Receiver};

#[derive(Debug)]
pub(crate) enum AudioFileDecodeError {
    OpenFile,
    OpenAudioStream,
}

pub(crate) fn decode_audio_file(
    source_url: &str,
) -> Result<Receiver<ffmpeg_next::frame::Audio>, AudioFileDecodeError> {
    let (frame_sender, frame_receiver) = channel();

    let mut input_context =
        ffmpeg_next::format::input(&source_url.to_string()).expect("Unable to open file");
    let input_stream = input_context
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .expect("Unable to get audio stream");
    let mut decoder = input_stream
        .codec()
        .decoder()
        .audio()
        .expect("Unable to get audio decoder");

    decoder.set_parameters(input_stream.parameters()).unwrap();

    std::thread::spawn(move || {
        let mut resampler = ffmpeg_next::software::resampling::Context::get(
            decoder.format(),
            ChannelLayout::STEREO,
            decoder.rate(),
            decoder.format(),
            ChannelLayout::STEREO,
            decoder.rate(),
        )
        .expect("Unable to initialize resampler");

        for (_, packet) in input_context.packets() {
            unwrap_or_return!(decoder.send_packet(&packet));
            let mut decoded = frame::Audio::empty();
            while decoder.receive_frame(&mut decoded).is_ok() {
                let mut resampled = frame::Audio::empty();
                unwrap_or_return!(resampler.run(&decoded, &mut resampled));
                unwrap_or_return!(frame_sender.send(resampled));
            }
        }

        unwrap_or_return!(decoder.send_eof());
        let mut decoded = frame::Audio::empty();
        while decoder.receive_frame(&mut decoded).is_ok() {
            let mut resampled = frame::Audio::empty();
            unwrap_or_return!(resampler.run(&decoded, &mut resampled));
            unwrap_or_return!(frame_sender.send(resampled));
        }
    });

    Ok(frame_receiver)
}

#[cfg(test)]
mod tests {
    #[test]
    fn test_decode_audio_source() {
        let decoder = super::decode_audio_file("tests/fixtures/decoder_test_file.wav").unwrap();

        while let Ok(a) = decoder.recv() {
            eprintln!("f: {:?}", a.pts())
        }

        assert!(false);
    }
}
