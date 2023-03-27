use crate::stream::constants::{AUDIO_SAMPLING_FREQUENCY, INTERNAL_TIME_BASE};
use crate::stream::ffmpeg::utils::convert_sample_to_byte_data;
use crate::stream::ffmpeg::INTERNAL_CHANNEL_LAYOUT;
use crate::stream::types::Buffer;
use crate::unwrap_or_return;
use actix_web::web::Bytes;
use ffmpeg_next::format::context::Input;
use ffmpeg_next::format::sample::Type;
use ffmpeg_next::format::Sample;
use ffmpeg_next::frame::Audio;
use ffmpeg_next::option::Type::SampleFormat;
use ffmpeg_next::{decoder, frame, rescale, ChannelLayout, Rational, Rescale};
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
        let resampler_time_base = (1, AUDIO_SAMPLING_FREQUENCY as i32);
        let pts = self
            .pts()
            .unwrap_or_default()
            .rescale(resampler_time_base, INTERNAL_TIME_BASE) as u64;
        let data_len = self.samples() * 4; // number of bytes required to represent each pair of 16-bit integers as four 8-bit integers

        eprintln!("pts2: {:?}", pts);

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
        let position_millis =
            (offset.as_millis() as i64).rescale(INTERNAL_TIME_BASE, rescale::TIME_BASE);
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

    let decoder_time_base = decoder.time_base();

    let mut resampler = decoder
        .resampler(
            Sample::I16(Type::Packed),
            INTERNAL_CHANNEL_LAYOUT,
            AUDIO_SAMPLING_FREQUENCY as u32,
        )
        .map_err(|error| AudioFileDecodeError::AudioDecoder(error))?;
    let resampler_time_base = (1, AUDIO_SAMPLING_FREQUENCY as i32);

    std::thread::spawn(move || {
        let mut receive_and_process_decoded_frames = move |decoder: &mut decoder::Audio| {
            let mut decoded = frame::Audio::empty();
            while decoder.receive_frame(&mut decoded).is_ok() {
                let rescaled_ts = decoded
                    .pts()
                    .map(|pts| pts.rescale(input_time_base, decoder_time_base));
                decoded.set_pts(rescaled_ts);
                let mut resampled = frame::Audio::empty();
                resampled.clone_from(&decoded);
                unwrap_or_return!(resampler.run(&decoded, &mut resampled));
                // let rescaled_ts = resampled
                //     .pts()
                //     .map(|pts| pts.rescale(resampler_time_base, INTERNAL_TIME_BASE));
                // resampled.set_pts(rescaled_ts);
                unwrap_or_return!(frame_sender.send(resampled.into()));
            }
        };

        for (_, mut packet) in ictx.packets() {
            // packet.rescale_ts(input_time_base, decoder_time_base);
            eprintln!("pts: {:?}", packet.pts());
            unwrap_or_return!(decoder.send_packet(&packet));
            receive_and_process_decoded_frames(&mut decoder);
        }

        unwrap_or_return!(decoder.send_eof());
        receive_and_process_decoded_frames(&mut decoder);
    });

    Ok(frame_receiver)
}

fn receive_and_process_decoded_frames(
    decoder: &mut ffmpeg_next::decoder::Audio,
    resampler: &mut ffmpeg_next::software::resampling::Context,
) -> Result<Vec<Audio>, ()> {
    let mut frames = vec![];

    let mut decoded = frame::Audio::empty();

    while decoder.receive_frame(&mut decoded).is_ok() {
        let rescaled_ts = decoded
            .pts()
            .map(|pts| pts.rescale(input_time_base, decoder_time_base));
        decoded.set_pts(rescaled_ts);
        let mut resampled = frame::Audio::empty();
        resampled.clone_from(&decoded);
        resampler.run(&decoded, &mut resampled)?;
        // let rescaled_ts = resampled
        //     .pts()
        //     .map(|pts| pts.rescale(resampler_time_base, INTERNAL_TIME_BASE));
        // resampled.set_pts(rescaled_ts);
        frames.push(resampled);
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
