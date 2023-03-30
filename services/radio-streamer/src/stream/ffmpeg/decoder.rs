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
use ffmpeg_next::{frame, rescale, ChannelLayout, Rational, Rescale};
use futures::channel::mpsc::{channel, Receiver, SendError, Sender};
use futures::SinkExt;
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

fn process_audio_stream_packets(
    mut packets: ffmpeg_next::format::context::input::PacketIter,
    input_time_base: &Rational,
    decoder: &mut ffmpeg_next::decoder::Audio,
    resampler: &mut ffmpeg_next::software::resampling::Context,
    frame_sender: &mut Sender<SharedFrame>,
) -> Result<(), ProcessAudioStreamPacketsError> {
    let runtime = Runtime::new().expect("Unable to init async runtime");

    for (_, mut packet) in packets {
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
        frames.push(resampled_frame);
    }

    Ok(frames)
}

#[cfg(test)]
mod tests {
    use crate::stream::types::SharedFrame;
    use futures::StreamExt;
    use std::time::Duration;

    #[actix_rt::test]
    async fn test_decoding_wav() {
        let mut decoded_frames =
            super::decode_audio_file("tests/fixtures/test_file.wav", &Duration::default()).unwrap();

        let mut frames_count = 0;
        let mut max_pts = Duration::from_secs(0);
        let mut min_pts = Duration::from_secs(u64::MAX);

        let mut last_frame = None;

        while let Some(frame) = decoded_frames.next().await {
            last_frame = Some(frame);
        }

        assert_eq!(
            Some(SharedFrame::new(
                Duration::from_millis(2603),
                Duration::from_millis(1),
                vec![
                    134, 141, 4, 245, 50, 164, 165, 248, 119, 253, 139, 12, 7, 71, 5, 214, 255,
                    230, 222, 11, 247, 74, 69, 120, 46, 244, 2, 27, 249, 188, 151, 130, 249, 0,
                    241, 43, 71, 138, 45, 113, 20, 176, 38, 181, 189, 163, 80, 218, 103, 140, 105,
                    228, 102, 33, 129, 32, 25, 179, 149, 51, 151, 15, 0, 128, 16, 103, 152, 245,
                    90, 216, 3, 94, 229, 39, 219, 167, 192, 52, 168, 164, 111, 102, 67, 189, 25,
                    70, 145, 229, 32, 201, 182, 235, 162, 155, 29, 254, 196, 37, 170, 13, 255, 127,
                    26, 87, 124, 18, 120, 245, 243, 243, 186, 213, 17, 173, 255, 127, 150, 172, 38,
                    40, 83, 56, 0, 128, 133, 75, 247, 223, 250, 54, 251, 39, 162, 186, 152, 179,
                    169, 254, 142, 140, 188, 219, 4, 177, 180, 152, 19, 73, 255, 127, 126, 47, 240,
                    6, 0, 128, 49, 69, 49, 31, 200, 33, 177, 116, 69, 149, 16, 46, 255, 127, 203,
                    70, 190, 100, 104, 211, 146, 121, 71, 168, 65, 148, 237, 3, 0, 128, 66, 239,
                    168, 83, 205, 164, 206, 185, 51, 106, 4, 8, 73, 3, 34, 189, 130, 159, 187, 236,
                    112, 186, 210, 110, 14, 143, 231, 239, 33, 0, 30, 88, 187, 43, 6, 228, 88, 46,
                    91, 166, 105, 242, 79, 103, 172, 56, 255, 127, 207, 126, 193, 170, 248, 102,
                    132, 46, 59, 16, 27, 42, 130, 158, 7, 208, 198, 75, 79, 239, 119, 42
                ],
            )),
            last_frame
        );
    }

    #[actix_rt::test]
    async fn test_decoding_offset() {
        let mut decoded_frames =
            super::decode_audio_file("tests/fixtures/test_file.wav", &Duration::from_millis(1200))
                .unwrap();

        let mut frames_count = 0;
        let mut max_pts = Duration::from_secs(0);
        let mut min_pts = Duration::from_secs(u64::MAX);

        while let Some(frame) = decoded_frames.next().await {
            frames_count += 1;
            max_pts = frame.pts().clone();
            min_pts = max_pts.min(min_pts);
        }

        assert_eq!(71, frames_count);
        assert_eq!(2596, max_pts.as_millis());
        assert_eq!(1103, min_pts.as_millis());
    }
}
