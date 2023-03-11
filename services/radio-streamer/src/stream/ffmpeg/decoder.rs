use crate::unwrap_or_return;
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
        let mut frame = ffmpeg_next::frame::audio::Audio::empty();

        for (_, packet) in input_context.packets() {
            unwrap_or_return!(decoder.send_packet(&packet));
            while decoder.receive_frame(&mut frame).is_ok() {
                unwrap_or_return!(frame_sender.send(frame.clone()));
            }
        }

        unwrap_or_return!(decoder.send_eof());
        while decoder.receive_frame(&mut frame).is_ok() {
            unwrap_or_return!(frame_sender.send(frame.clone()));
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
