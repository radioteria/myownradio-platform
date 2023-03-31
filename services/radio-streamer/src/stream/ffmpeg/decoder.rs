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
    async fn test_decoding_flac() {
        let mut decoded_frames =
            super::decode_audio_file("tests/fixtures/test_file.flac", &Duration::default())
                .unwrap();

        let mut last_frame = None;

        while let Some(frame) = decoded_frames.next().await {
            last_frame = Some(frame);
        }

        assert_eq!(
            Some(SharedFrame::new(
                Duration::from_millis(2592),
                Duration::from_millis(12),
                vec![
                    234, 198, 0, 128, 87, 56, 234, 156, 184, 17, 230, 85, 97, 139, 250, 25, 246,
                    62, 239, 146, 37, 43, 187, 89, 171, 119, 134, 75, 192, 2, 36, 198, 107, 144,
                    174, 138, 245, 10, 201, 10, 10, 246, 36, 19, 129, 6, 239, 163, 130, 177, 12,
                    197, 168, 255, 96, 7, 24, 13, 230, 29, 103, 151, 231, 141, 99, 79, 94, 248, 72,
                    222, 209, 203, 12, 129, 142, 47, 236, 246, 132, 198, 122, 41, 96, 153, 160, 4,
                    44, 10, 59, 248, 217, 60, 225, 16, 236, 60, 253, 12, 31, 228, 210, 231, 52, 46,
                    0, 128, 17, 52, 101, 152, 172, 74, 116, 181, 252, 202, 150, 255, 59, 51, 177,
                    236, 183, 64, 27, 222, 126, 207, 31, 214, 111, 24, 233, 48, 56, 1, 168, 52,
                    156, 50, 92, 20, 33, 104, 106, 7, 255, 127, 111, 190, 61, 100, 63, 31, 238, 49,
                    151, 249, 13, 46, 146, 87, 199, 186, 51, 122, 195, 41, 56, 188, 21, 25, 191,
                    187, 125, 108, 100, 135, 31, 228, 15, 234, 223, 223, 195, 111, 255, 127, 155,
                    182, 11, 74, 130, 167, 18, 79, 96, 30, 12, 10, 180, 18, 59, 28, 191, 253, 235,
                    127, 139, 170, 183, 106, 0, 128, 104, 173, 212, 77, 192, 30, 79, 123, 255, 127,
                    103, 93, 211, 27, 155, 64, 135, 138, 78, 248, 198, 23, 163, 45, 86, 28, 57, 83,
                    252, 10, 209, 204, 52, 31, 180, 142, 198, 13, 221, 86, 102, 115, 17, 0, 11,
                    213, 0, 128, 213, 100, 36, 33, 191, 198, 251, 159, 76, 132, 90, 236, 132, 179,
                    247, 116, 0, 128, 172, 109, 206, 163, 200, 108, 0, 128, 252, 61, 20, 249, 137,
                    248, 86, 33, 44, 29, 6, 152, 87, 84, 16, 240, 163, 205, 108, 119, 166, 24, 209,
                    6, 72, 197, 167, 153, 197, 58, 98, 227, 181, 110, 143, 230, 220, 66, 196, 167,
                    74, 245, 233, 50, 158, 195, 251, 124, 50, 194, 245, 226, 9, 140, 24, 206, 25,
                    51, 44, 18, 180, 236, 176, 144, 133, 1, 246, 141, 103, 56, 12, 69, 55, 179,
                    158, 196, 75, 87, 0, 128, 86, 199, 93, 238, 134, 235, 184, 174, 171, 218, 150,
                    14, 158, 241, 201, 76, 212, 92, 157, 180, 102, 161, 70, 5, 165, 55, 239, 96,
                    170, 101, 65, 35, 119, 179, 129, 150, 23, 137, 116, 204, 29, 63, 255, 127, 80,
                    251, 188, 192, 188, 202, 239, 133, 119, 114, 121, 58, 79, 212, 90, 46, 120,
                    199, 118, 80, 249, 28, 32, 238, 96, 114, 137, 22, 164, 59, 112, 57, 0, 128, 23,
                    182, 39, 7, 186, 64, 22, 236, 33, 10, 91, 153, 121, 201, 87, 162, 98, 8, 74,
                    94, 22, 90, 157, 222, 191, 228, 185, 70, 162, 233, 153, 30, 255, 127, 213, 9,
                    227, 69, 240, 74, 62, 19, 63, 137, 248, 180, 181, 18, 144, 206, 0, 128, 169,
                    40, 38, 172, 28, 144, 185, 127, 136, 192, 197, 175, 62, 29, 123, 53, 10, 7, 83,
                    105, 61, 42, 232, 180, 160, 63, 140, 220, 31, 11, 112, 64, 171, 167, 134, 36,
                    251, 198, 122, 240, 183, 171, 51, 233, 0, 151, 92, 193, 75, 250, 230, 252, 119,
                    54, 48, 82, 105, 65, 233, 29, 226, 158, 182, 147, 173, 183, 210, 7, 65, 230,
                    180, 252, 171, 31, 134, 148, 93, 248, 243, 210, 127, 16, 0, 128, 197, 36, 12,
                    215, 100, 216, 47, 91, 46, 52, 155, 125, 54, 191, 44, 90, 154, 145, 94, 148,
                    247, 57, 52, 38, 239, 113, 214, 127, 134, 252, 247, 117, 49, 166, 85, 213, 122,
                    88, 4, 188, 184, 238, 196, 79, 0, 128, 211, 19, 175, 55, 222, 64, 205, 162,
                    245, 254, 252, 236, 122, 138, 240, 81, 150, 234, 77, 222, 99, 241, 40, 70, 245,
                    221, 152, 224, 227, 212, 178, 61, 5, 184, 90, 212, 66, 220, 0, 128, 83, 75, 74,
                    130, 127, 38, 213, 246, 150, 43, 255, 127, 97, 44, 220, 239, 80, 130, 164, 147,
                    36, 217, 103, 199, 229, 35, 0, 128, 114, 177, 77, 199, 131, 13, 235, 56, 217,
                    117, 165, 240, 71, 209, 101, 88, 0, 128, 217, 104, 232, 49, 175, 58, 180, 47,
                    188, 163, 167, 30, 152, 190, 70, 86, 4, 202, 71, 173, 141, 188, 103, 21, 81,
                    232, 82, 34, 0, 128, 4, 152, 26, 191, 186, 8, 195, 209, 56, 193, 80, 70, 115,
                    217, 52, 225, 145, 78, 42, 153, 176, 194, 120, 154, 9, 211, 200, 184, 29, 4,
                    115, 251, 121, 25, 137, 102, 170, 239, 99, 2, 29, 178, 219, 146, 182, 93, 255,
                    127, 228, 100, 14, 83, 220, 11, 105, 130, 156, 213, 164, 231, 114, 133, 52,
                    216, 168, 214, 237, 132, 251, 66, 120, 237, 145, 118, 119, 247, 40, 41, 207,
                    175, 179, 24, 222, 78, 25, 118, 47, 254, 250, 75, 216, 2, 181, 52, 20, 71, 222,
                    64, 251, 210, 195, 194, 152, 54, 36, 129, 181, 144, 232, 223, 21, 174, 205,
                    156, 112, 204, 128, 251, 118, 157, 163, 116, 156, 36, 223, 23, 200, 247, 85,
                    40, 104, 220, 228, 120, 97, 25, 202, 90, 42, 250, 37, 79, 106, 239, 105, 45,
                    60, 178, 156, 242, 151, 15, 33, 253, 34, 116, 96, 169, 195, 211, 41, 209, 108,
                    43, 95, 212, 232, 111, 0, 128, 255, 127, 230, 240, 49, 60, 102, 157, 193, 80,
                    109, 29, 62, 202, 12, 88, 6, 208, 72, 234, 166, 68, 80, 105, 0, 167, 33, 138,
                    255, 127, 226, 162, 158, 43, 202, 207, 53, 173, 79, 172, 209, 116, 49, 105,
                    180, 22, 132, 76, 91, 243, 154, 54, 7, 14, 53, 95, 166, 230, 255, 127, 59, 180,
                    224, 22, 57, 174, 139, 197, 92, 71, 178, 56, 2, 236, 194, 118, 93, 19, 239,
                    119, 103, 97, 64, 231, 251, 174, 151, 92, 14, 106, 236, 11, 208, 75, 53, 18,
                    205, 206, 208, 99, 117, 183, 124, 26, 45, 212, 239, 46, 13, 43, 222, 214, 100,
                    84, 235, 45, 249, 246, 83, 33, 137, 202, 73, 196, 206, 59, 151, 66, 164, 27,
                    200, 88, 251, 79, 83, 123, 96, 249, 224, 195, 176, 143, 109, 216, 116, 165, 38,
                    221, 198, 69, 108, 160, 85, 123, 0, 128, 20, 88, 186, 224, 48, 188, 144, 67,
                    237, 197, 179, 238, 255, 127, 130, 57, 36, 25, 209, 27, 228, 182, 118, 255, 29,
                    219, 235, 251, 95, 26, 131, 40, 0, 222, 151, 169, 111, 191, 194, 208, 255, 127,
                    31, 212, 48, 186, 230, 232, 60, 227, 145, 210, 181, 90, 72, 161, 222, 203, 142,
                    61, 38, 235, 231, 176, 225, 9, 109, 156, 41, 201, 6, 171, 166, 161, 219, 150,
                    181, 108, 152, 93, 170, 208, 10, 211, 113, 222, 44, 240, 179, 81, 21, 4, 158,
                    95, 129, 218, 53, 212, 31, 249, 61, 213, 166, 14, 157, 9, 150, 126, 56, 166,
                    45, 180, 83, 171, 125, 221, 32, 147, 97, 108, 221, 249, 209, 182, 255, 33, 115,
                    195, 240, 23, 96, 233, 93, 205, 82, 46, 157, 40, 203, 41, 4, 71, 19, 255, 0,
                    177, 67, 249, 130, 230, 21, 56, 83, 137, 64, 67, 58, 220, 0, 128, 126, 122, 93,
                    220, 10, 1, 223, 95, 38, 205, 86, 42, 117, 3, 176, 94, 89, 93, 220, 75, 200,
                    140, 255, 127, 226, 78, 102, 237, 52, 21, 188, 194, 213, 237, 75, 62, 255, 127,
                    200, 42, 118, 22, 92, 243, 0, 128, 226, 232, 229, 235, 255, 127, 21, 114, 161,
                    89, 80, 243, 193, 191, 236, 88, 119, 208, 82, 17, 69, 205, 253, 252, 32, 52,
                    98, 255, 169, 201, 12, 57, 213, 165, 116, 15, 87, 50, 36, 213, 120, 203, 105,
                    88, 123, 93, 194, 1, 53, 22, 171, 36, 255, 169, 13, 27, 255, 127, 230, 170,
                    129, 208, 110, 7, 240, 234, 103, 149, 119, 237, 71, 181, 161, 131, 181, 4, 44,
                    26, 220, 222, 103, 28, 143, 190, 204, 201, 103, 152, 72, 210, 146, 5, 142, 255,
                    45, 97, 165, 35, 118, 83, 135, 33, 95, 25, 159, 19, 81, 54, 202, 135, 235, 40,
                    88, 147, 191, 129, 184, 79, 212, 254, 56, 42, 164, 119, 211, 202, 125, 243, 26,
                    151, 192, 131, 17, 39, 195, 164, 212, 90, 12, 160, 114, 33, 209, 23, 101, 191,
                    150, 21, 201, 184, 27, 143, 177, 29, 249, 164, 111, 48, 29, 6, 144, 84, 112,
                    67, 77, 147, 96, 147, 210, 2, 91, 17, 214, 56, 82, 89, 73, 156, 196, 42, 78,
                    130, 37, 253, 13, 228, 238, 227, 42, 231, 121, 240, 0, 128, 49, 213, 24, 245,
                    34, 248, 154, 35, 168, 17, 213, 166, 175, 8, 164, 140, 127, 160, 251, 167, 43,
                    175, 64, 6, 9, 7, 56, 182, 157, 181, 181, 79, 63, 221, 224, 101, 173, 126, 91,
                    37, 137, 1, 3, 61, 137, 40, 95, 15, 150, 62, 177, 45, 166, 250, 255, 127, 49,
                    51, 157, 79, 58, 57, 108, 135, 186, 101, 80, 32, 86, 17, 194, 21, 1, 239, 250,
                    182, 178, 139, 214, 2, 221, 237, 255, 127, 190, 21, 66, 229, 0, 128, 0, 128,
                    122, 217, 19, 208, 162, 218, 48, 49, 181, 190, 143, 215, 68, 121, 183, 18, 241,
                    26, 170, 59, 41, 43, 130, 219, 90, 123, 111, 73, 81, 159, 224, 172, 14, 230,
                    206, 76, 192, 246, 124, 218, 172, 18, 78, 181, 35, 99, 255, 47, 79, 36, 253,
                    48, 231, 182, 64, 204, 184, 195, 223, 169, 255, 127, 26, 77, 224, 69, 0, 128,
                    0, 128, 55, 196, 169, 180, 201, 78, 234, 27, 31, 119, 25, 219, 225, 107, 40,
                    85, 44, 5, 158, 53, 54, 231, 66, 53, 138, 143, 205, 102, 135, 159, 168, 69, 52,
                    233, 85, 85, 157, 93, 25, 26, 220, 58, 50, 59, 250, 213, 246, 27, 205, 122,
                    185, 230, 173, 224, 56, 3, 141, 221, 13, 223, 89, 116, 88, 100, 61, 197, 107,
                    124, 119, 206, 186, 8, 85, 156, 47, 36, 129, 0, 42, 248, 55, 97, 75, 218, 115,
                    165, 153, 50, 81, 177, 255, 127, 106, 179, 47, 105, 192, 153, 4, 110, 23, 140,
                    231, 110, 226, 218, 207, 223, 78, 70, 106, 56, 45, 36, 206, 215, 205, 170, 96,
                    205, 101, 158, 246, 208, 221, 31, 171, 193, 96, 96, 46, 255, 33, 64, 91, 214,
                    64, 225, 31, 187, 190, 255, 89, 10, 153, 84, 191, 125, 161, 243, 125, 19, 121,
                    246, 146, 241, 37, 21, 109, 226, 66, 241, 101, 168, 177, 203, 0, 128, 205, 39,
                    113, 31, 80, 93, 178, 18, 192, 106, 8, 228, 16, 29, 169, 117, 25, 7, 61, 246,
                    51, 117, 99, 153, 48, 114, 226, 64, 135, 43, 254, 63, 194, 157, 162, 134, 58,
                    195, 82, 22, 160, 16, 128, 118, 246, 53, 89, 244, 157, 153, 201, 38, 190, 211,
                    172, 255, 211, 72, 140, 228, 96, 53, 159, 192, 99, 120, 245, 202, 10, 10, 137,
                    233, 105, 55, 55, 241, 46, 249, 164, 97, 216, 156, 64, 173, 162, 84, 105, 131,
                    8, 168, 18, 234, 154, 191, 161, 63, 71, 247, 252, 239, 145, 236, 75, 190, 107,
                    93, 209, 254, 48, 241, 167, 98, 113, 148, 20, 38, 38, 239, 20, 26, 255, 127,
                    10, 118, 72, 85, 9, 151, 90, 49, 135, 249, 226, 126, 255, 127, 35, 230, 144,
                    197, 113, 138, 139, 6, 146, 182, 40, 88, 107, 85, 191, 23, 47, 54, 15, 169,
                    232, 206, 233, 179, 213, 10, 54, 199, 155, 187, 48, 165, 252, 23, 162, 144, 12,
                    47, 11, 218, 251, 247, 239, 80, 126, 56, 0, 136, 106, 182, 252, 219, 68, 0, 73,
                    85, 90, 173, 171, 2, 249, 15, 147, 2, 22, 100, 81, 191, 106, 224, 27, 37, 79,
                    208, 49, 119, 247, 3, 164, 50, 49, 181, 136, 225, 101, 165, 33, 155, 1, 33, 0,
                    128, 31, 186, 0, 135, 210, 49, 16, 75, 131, 166, 236, 211, 31, 210, 45, 162,
                    101, 216, 200, 48, 134, 141, 4, 245, 50, 164, 165, 248, 119, 253, 139, 12, 7,
                    71, 5, 214, 255, 230, 222, 11, 247, 74, 69, 120, 46, 244, 2, 27, 249, 188, 151,
                    130, 249, 0, 241, 43, 71, 138, 45, 113, 20, 176, 38, 181, 189, 163, 80, 218,
                    103, 140, 105, 228, 102, 33, 129, 32, 25, 179, 149, 51, 151, 15, 0, 128, 16,
                    103, 152, 245, 90, 216, 3, 94, 229, 39, 219, 167, 192, 52, 168, 164, 111, 102,
                    67, 189, 25, 70, 145, 229, 32, 201, 182, 235, 162, 155, 29, 254, 196, 37, 170,
                    13, 255, 127, 26, 87, 124, 18, 120, 245, 243, 243, 186, 213, 17, 173, 255, 127,
                    150, 172, 38, 40, 83, 56, 0, 128, 133, 75, 247, 223, 250, 54, 251, 39, 162,
                    186, 152, 179, 169, 254, 142, 140, 188, 219, 4, 177, 180, 152, 19, 73, 255,
                    127, 126, 47, 240, 6, 0, 128, 49, 69, 49, 31, 200, 33, 177, 116, 69, 149, 16,
                    46, 255, 127, 203, 70, 190, 100, 104, 211, 146, 121, 71, 168, 65, 148, 237, 3,
                    0, 128, 66, 239, 168, 83, 205, 164, 206, 185, 51, 106, 4, 8, 73, 3, 34, 189,
                    130, 159, 187, 236, 112, 186, 210, 110, 14, 143, 231, 239, 33, 0, 30, 88, 187,
                    43, 6, 228, 88, 46, 91, 166, 105, 242, 79, 103, 172, 56, 255, 127, 207, 126,
                    193, 170, 248, 102, 132, 46, 59, 16, 27, 42, 130, 158, 7, 208, 198, 75, 79,
                    239, 119, 42
                ],
            )),
            last_frame
        );
    }

    #[actix_rt::test]
    async fn test_decoding_mp3() {
        let mut decoded_frames =
            super::decode_audio_file("tests/fixtures/test_file.mp3", &Duration::default()).unwrap();

        let mut last_frame = None;

        while let Some(frame) = decoded_frames.next().await {
            last_frame = Some(frame);
        }

        assert_eq!(
            Some(SharedFrame::new(
                Duration::from_millis(2616),
                Duration::from_millis(11),
                vec![
                    246, 99, 173, 66, 8, 55, 136, 61, 231, 242, 239, 86, 224, 244, 255, 127, 168,
                    224, 103, 75, 243, 228, 166, 251, 160, 79, 110, 12, 94, 101, 251, 20, 141, 212,
                    65, 248, 24, 130, 129, 44, 66, 182, 82, 84, 122, 220, 7, 0, 19, 238, 195, 231,
                    6, 21, 205, 87, 95, 16, 138, 118, 171, 248, 170, 29, 218, 22, 252, 1, 201, 28,
                    224, 46, 31, 208, 51, 69, 253, 154, 240, 73, 31, 203, 62, 62, 87, 49, 75, 8,
                    118, 118, 56, 220, 41, 92, 30, 242, 189, 20, 92, 38, 171, 17, 79, 45, 41, 52,
                    242, 247, 246, 22, 174, 231, 136, 227, 6, 58, 86, 220, 30, 103, 40, 232, 237,
                    25, 80, 245, 248, 239, 119, 220, 97, 8, 32, 153, 54, 186, 60, 217, 0, 128, 255,
                    127, 61, 145, 255, 127, 82, 189, 38, 252, 12, 150, 24, 3, 211, 214, 240, 108,
                    26, 32, 187, 44, 8, 216, 193, 246, 173, 222, 34, 37, 188, 65, 170, 218, 199,
                    234, 14, 164, 0, 128, 121, 79, 17, 226, 255, 127, 23, 11, 213, 57, 218, 184,
                    139, 230, 39, 10, 249, 212, 255, 127, 241, 154, 68, 44, 67, 153, 173, 167, 48,
                    207, 63, 153, 220, 247, 20, 207, 119, 58, 20, 23, 144, 71, 45, 6, 19, 3, 105,
                    164, 81, 17, 53, 166, 108, 38, 225, 199, 51, 196, 159, 165, 194, 183, 127, 254,
                    209, 2, 97, 127, 163, 210, 126, 30, 228, 220, 81, 212, 57, 113, 200, 74, 248,
                    68, 33, 34, 110, 170, 46, 131, 21, 216, 233, 214, 183, 9, 106, 78, 17, 211,
                    255, 236, 208, 27, 113, 160, 9, 89, 82, 204, 28, 215, 78, 254, 208, 171, 173,
                    76, 58, 33, 124, 111, 48, 67, 212, 28, 158, 32, 222, 249, 22, 7, 87, 30, 152,
                    195, 137, 13, 35, 176, 66, 11, 141, 231, 143, 28, 65, 219, 186, 221, 97, 190,
                    100, 203, 156, 252, 61, 36, 252, 57, 82, 69, 235, 74, 96, 66, 197, 75, 206,
                    101, 253, 22, 184, 42, 158, 219, 24, 171, 145, 205, 110, 160, 219, 164, 66,
                    212, 163, 143, 249, 217, 127, 238, 210, 241, 37, 63, 228, 36, 107, 27, 136, 47,
                    140, 246, 141, 27, 160, 255, 93, 10, 150, 254, 148, 5, 167, 16, 217, 246, 90,
                    71, 37, 178, 156, 68, 0, 128, 112, 222, 140, 162, 47, 156, 164, 13, 3, 244,
                    130, 48, 251, 79, 118, 1, 172, 0, 165, 207, 65, 166, 114, 207, 88, 233, 78,
                    240, 193, 21, 135, 14, 175, 173, 67, 36, 0, 128, 203, 21, 135, 182, 73, 229,
                    75, 37, 38, 240, 154, 84, 134, 25, 89, 69, 110, 226, 231, 29, 110, 188, 99,
                    249, 77, 45, 36, 0, 111, 117, 34, 98, 8, 30, 255, 127, 44, 205, 46, 8, 28, 189,
                    0, 128, 133, 193, 215, 230, 27, 246, 255, 127, 163, 16, 209, 57, 240, 214, 231,
                    201, 198, 205, 44, 193, 110, 11, 123, 202, 148, 29, 250, 230, 193, 26, 253, 6,
                    155, 36, 110, 226, 244, 14, 195, 198, 196, 230, 27, 238, 10, 176, 78, 25, 255,
                    140, 81, 51, 225, 233, 79, 22, 93, 96, 94, 201, 224, 48, 254, 217, 206, 195,
                    31, 20, 133, 158, 74, 226, 87, 144, 236, 228, 229, 179, 12, 88, 190, 254, 143,
                    55, 55, 7, 83, 175, 16, 42, 149, 177, 255, 127, 9, 225, 113, 84, 215, 236, 48,
                    212, 9, 45, 14, 202, 198, 62, 215, 190, 99, 251, 61, 149, 192, 252, 125, 198,
                    166, 24, 151, 187, 110, 252, 0, 128, 20, 243, 241, 200, 205, 218, 113, 103,
                    170, 181, 136, 46, 9, 248, 150, 184, 69, 34, 101, 163, 244, 215, 15, 195, 217,
                    222, 76, 19, 156, 22, 82, 28, 219, 233, 189, 179, 182, 250, 163, 211, 146, 74,
                    53, 77, 173, 27, 6, 19, 254, 247, 82, 209, 211, 16, 248, 10, 102, 172, 57, 211,
                    172, 144, 112, 137, 166, 78, 108, 244, 255, 127, 111, 26, 133, 32, 205, 198,
                    197, 78, 74, 253, 176, 119, 149, 78, 138, 32, 47, 28, 178, 43, 220, 9, 246, 69,
                    142, 32, 51, 205, 186, 249, 209, 144, 192, 212, 46, 188, 61, 163, 77, 191, 0,
                    128, 168, 242, 83, 179, 219, 68, 5, 5, 42, 34, 14, 232, 242, 23, 185, 7, 31,
                    119, 59, 57, 255, 127, 63, 203, 18, 73, 82, 151, 126, 47, 127, 14, 66, 246,
                    230, 36, 106, 193, 31, 243, 62, 223, 165, 66, 18, 228, 255, 126, 40, 190, 244,
                    81, 226, 197, 93, 94, 221, 189, 41, 116, 90, 159, 72, 30, 127, 224, 3, 215,
                    105, 76, 220, 236, 237, 99, 59, 16, 251, 49, 84, 25, 119, 210, 56, 4, 0, 128,
                    225, 233, 0, 128, 64, 11, 215, 243, 83, 65, 192, 93, 99, 44, 49, 103, 45, 247,
                    181, 72, 57, 245, 31, 73, 166, 246, 47, 78, 143, 195, 71, 29, 9, 172, 190, 235,
                    177, 241, 254, 14, 179, 53, 104, 90, 8, 44, 195, 108, 144, 27, 169, 59, 186,
                    38, 131, 21, 163, 40, 14, 61, 30, 47, 23, 106, 201, 24, 119, 50, 4, 197, 203,
                    228, 66, 197, 185, 237, 145, 51, 29, 15, 233, 46, 237, 16, 180, 200, 60, 6,
                    253, 217, 110, 241, 234, 22, 105, 20, 128, 18, 35, 122, 239, 54, 106, 115, 81,
                    58, 228, 248, 236, 175, 74, 206, 67, 145, 155, 203, 160, 83, 75, 142, 255, 127,
                    250, 159, 48, 45, 57, 252, 109, 169, 134, 1, 227, 222, 150, 251, 66, 83, 244,
                    47, 79, 65, 77, 38, 205, 206, 96, 0, 2, 190, 176, 15, 212, 241, 182, 247, 14,
                    235, 206, 201, 30, 6, 219, 218, 176, 67, 27, 211, 202, 248, 147, 183, 141, 172,
                    26, 235, 105, 27, 238, 249, 17, 87, 46, 175, 90, 211, 185, 177, 55, 148, 106,
                    226, 82, 219, 16, 187, 53, 251, 254, 178, 15, 8, 114, 12, 77, 50, 103, 38, 47,
                    35, 12, 241, 20, 245, 198, 236, 239, 11, 197, 3, 164, 61, 25, 235, 188, 35, 61,
                    213, 206, 188, 193, 255, 0, 128, 243, 51, 186, 173, 251, 35, 3, 231, 125, 242,
                    203, 220, 126, 222, 140, 235, 193, 214, 19, 21, 71, 237, 204, 0, 17, 62, 76,
                    252, 151, 76, 22, 51, 126, 237, 41, 14, 4, 222, 229, 161, 154, 36, 89, 189,
                    104, 246, 81, 48, 96, 165, 4, 39, 204, 246, 215, 208, 226, 94, 178, 227, 67,
                    66, 60, 54, 20, 46, 4, 46, 11, 102, 17, 234, 72, 85, 39, 230, 207, 238, 118,
                    16, 133, 216, 11, 47, 52, 36, 69, 75, 152, 43, 3, 41, 244, 222, 36, 178, 25,
                    253, 6, 160, 255, 127, 39, 37, 255, 127, 210, 91, 82, 247, 123, 31, 77, 193,
                    41, 29, 135, 235, 155, 37, 143, 230, 126, 236, 104, 218, 116, 248, 194, 254,
                    38, 53, 140, 253, 52, 8, 180, 215, 201, 231, 143, 241, 97, 69, 86, 44, 29, 85,
                    95, 34, 131, 211, 107, 240, 208, 166, 85, 244, 115, 241, 226, 20, 160, 252,
                    133, 240, 110, 203, 170, 173, 190, 202, 39, 205, 92, 211, 12, 30, 17, 181, 80,
                    3, 162, 184, 145, 183, 171, 6, 158, 225, 60, 88, 156, 63, 51, 101, 110, 41, 56,
                    51, 166, 198, 159, 252, 7, 170, 109, 237, 96, 239, 1, 1, 35, 64, 65, 30, 3, 47,
                    224, 36, 172, 185, 83, 241, 161, 141, 87, 161, 239, 13, 49, 142, 255, 127, 178,
                    194, 246, 57, 82, 243, 32, 175, 206, 254, 247, 150, 37, 239, 5, 249, 64, 208,
                    175, 92, 79, 206, 208, 58, 176, 243, 147, 186, 231, 252, 156, 183, 44, 232,
                    230, 39, 162, 1, 231, 2, 159, 50, 0, 128, 92, 34, 60, 135, 206, 223, 145, 241,
                    235, 193, 202, 228, 165, 231, 185, 215, 192, 30, 163, 14, 96, 41, 148, 224,
                    123, 10, 88, 128, 36, 225, 180, 175, 185, 179, 177, 10, 220, 172, 227, 1, 201,
                    237, 73, 12, 8, 33, 159, 85, 217, 19, 175, 64, 86, 22, 63, 237, 11, 38, 147,
                    29, 228, 254, 255, 127, 100, 238, 141, 64, 151, 50, 131, 196, 140, 95, 138,
                    236, 169, 61, 57, 57, 155, 8, 70, 0, 116, 220, 225, 222, 104, 208, 181, 49,
                    102, 235, 37, 67, 211, 228, 57, 236, 7, 187, 67, 187, 161, 201, 215, 181, 49,
                    251, 135, 186, 119, 2, 19, 248, 182, 10, 178, 54, 38, 64, 228, 17, 71, 88, 224,
                    203, 9, 26, 106, 226, 234, 202, 218, 59, 177, 198, 36, 67, 83, 249, 100, 217,
                    144, 32, 81, 178, 31, 56, 6, 12, 166, 40, 20, 40, 109, 224, 160, 239, 187, 223,
                    126, 2, 245, 67, 171, 2, 180, 42, 61, 141, 61, 142, 61, 147, 4, 144, 2, 82, 49,
                    33, 255, 127, 140, 77, 107, 68, 110, 54, 164, 11, 57, 61, 89, 219, 127, 41, 89,
                    131, 126, 41, 40, 148, 233, 99, 23, 26, 194, 94, 22, 92, 49, 42, 235, 42, 37,
                    42, 26, 16, 220, 15, 180, 52, 138, 209, 225, 27, 166, 231, 69, 200, 107, 31,
                    234, 231, 152, 47, 60, 63, 3, 84, 106, 244, 106, 81, 165, 147, 167, 233, 158,
                    245, 38, 200, 245, 65, 236, 48, 25, 216, 10, 113, 109, 160, 129, 89, 210, 200,
                    169, 87, 8, 155, 35, 101, 0, 128, 105, 69, 242, 238, 196, 19, 243, 61, 214,
                    250, 173, 253, 206, 239, 32, 194, 46, 218, 108, 216, 74, 204, 40, 10, 228, 226,
                    33, 42, 125, 251, 64, 33, 200, 241, 11, 8, 103, 232, 41, 16, 207, 247, 143, 38,
                    194, 17, 252, 34, 26, 45, 32, 10, 87, 30, 68, 222, 1, 205, 30, 187, 119, 152,
                    38, 246, 18, 200, 87, 126, 41, 12, 255, 127, 12, 23, 180, 46, 222, 4, 39, 228,
                    156, 1, 82, 37, 87, 7, 47, 87, 109, 250, 89, 51, 82, 236, 2, 23, 114, 6, 76,
                    253, 187, 32, 233, 214, 113, 6, 20, 247, 17, 245, 250, 26, 77, 25, 171, 214,
                    143, 47, 171, 193, 209, 21, 246, 47, 212, 237, 17, 99, 200, 210, 120, 56, 220,
                    224, 107, 50, 204, 6, 224, 30, 86, 6, 27, 224, 139, 230, 230, 233, 126, 199,
                    189, 19, 250, 176, 21, 246, 171, 212, 67, 213, 19, 16, 48, 224, 251, 236, 198,
                    237, 171, 196, 133, 254, 39, 22, 181, 248, 148, 75, 238, 215, 228, 34, 19, 253,
                    67, 59, 28, 86, 142, 89, 20, 103, 239, 4, 123, 71, 96, 217, 13, 49, 206, 251,
                    136, 250, 74, 228, 250, 193, 240, 254, 235, 216, 188, 116, 25, 36, 55, 78, 122,
                    66, 149, 179, 91, 244, 39, 174, 186, 147, 87, 217, 73, 208, 23, 149, 154, 83,
                    204, 134, 129, 60, 86, 237, 124, 241, 160, 19, 205, 14, 83, 219, 192, 248, 251,
                    203, 20, 154, 81, 9, 110, 195, 166, 50, 17, 37, 114, 6, 1, 10, 154, 225, 49,
                    236, 242, 34, 4, 21, 86, 97, 63, 0, 227, 49, 23, 193, 20, 222, 72, 198, 37,
                    165, 188, 235, 217, 132, 75, 246, 32, 162, 143, 234, 52, 230, 225, 221, 141,
                    254, 39, 229, 117, 247, 45, 207, 79, 250, 0, 128, 134, 248, 0, 128, 229, 237,
                    116, 250, 78, 223, 138, 71, 223, 230, 65, 42, 46, 45, 162, 35, 206, 92, 182,
                    39, 249, 21, 3, 223, 206, 212, 126, 153, 20, 250, 156, 165, 213, 4, 237, 177,
                    26, 211, 178, 149, 116, 219, 0, 165, 1, 6, 182, 226, 122, 3, 190, 242, 132,
                    247, 169, 247, 208, 238, 48, 62, 57, 229, 132, 85, 27, 13, 214, 5, 72, 41, 174,
                    21, 96, 208, 255, 127, 0, 128, 155, 46, 204, 205, 0, 128, 200, 54, 25, 169,
                    189, 9, 255, 127, 91, 189, 255, 127, 88, 241, 136, 12, 126, 67, 178, 210, 125,
                    51, 146, 196, 225, 10, 11, 200, 75, 17, 180, 37, 90, 13, 8, 117, 253, 239, 55,
                    43, 34, 213, 21, 185, 253, 164, 90, 181, 106, 140, 35, 229, 3, 216, 167, 247,
                    205, 34, 78, 27, 180, 0, 28, 74, 9, 228, 162, 30, 156, 41, 137, 188, 213, 103,
                    179, 207, 22, 81, 122, 100, 224, 17, 255, 127, 159, 214, 93, 82, 241, 187, 150,
                    191, 218, 204, 115, 183, 153, 229, 89, 15, 243, 246, 229, 12, 73, 15, 198, 183,
                    143, 10, 61, 185, 200, 199
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
