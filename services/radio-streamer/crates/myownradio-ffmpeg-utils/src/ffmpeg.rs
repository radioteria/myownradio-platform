use crate::INTERNAL_TIME_BASE;
use ffmpeg_next::format::sample::Type::{Packed, Planar};
use ffmpeg_next::format::Sample::I16;
use ffmpeg_next::{
    codec, decoder,
    encoder::{audio::Encoder, find_by_name},
    filter, format, rescale, ChannelLayout, Error, Rescale,
};
use std::time::Duration;
use tracing::debug;

#[derive(Debug, thiserror::Error)]
pub enum SetupAudioDecoderError {
    #[error("Audio stream not found")]
    AudioStreamNotFound,
    #[error("FFmpeg returned error: {0}")]
    FFmpegError(#[from] Error),
}

pub(crate) fn setup_audio_decoder(
    ictx: &mut format::context::Input,
) -> Result<(usize, decoder::Audio, format::stream::Stream), SetupAudioDecoderError> {
    let stream = ictx
        .streams()
        .best(ffmpeg_next::media::Type::Audio)
        .ok_or_else(|| SetupAudioDecoderError::AudioStreamNotFound)?;
    let input_index = stream.index();
    let context = codec::Context::from_parameters(stream.parameters())?;

    let mut decoder = context.decoder().audio()?;

    decoder.set_parameters(stream.parameters())?;

    if decoder.channel_layout().is_empty() {
        decoder.set_channel_layout(ChannelLayout::default(decoder.channels() as i32));
    }

    Ok((input_index, decoder, stream))
}

#[derive(Debug, thiserror::Error)]
pub enum OpenInputError {
    #[error("FFmpeg returned error: {0}")]
    FFmpegError(#[from] Error),
}

pub(crate) fn open_input(
    source_url: &str,
    position: &Duration,
) -> Result<format::context::Input, OpenInputError> {
    let mut ictx = format::input(&source_url.to_string())
        .map_err(|error| OpenInputError::FFmpegError(error))?;

    if !position.is_zero() {
        let position_millis = position.as_millis() as i64;
        let rescaled_position = position_millis.rescale(INTERNAL_TIME_BASE, rescale::TIME_BASE);

        debug!(?position, "Setting up input initial position");

        ictx.seek(rescaled_position, ..rescaled_position)?;
    }

    Ok(ictx)
}

#[derive(Debug, thiserror::Error)]
pub enum SetupResamplingFilterError {
    #[error("FFmpeg returned error: {0}")]
    FFmpegError(#[from] Error),
}

pub(crate) fn setup_resampling_filter(
    sample_rate: u32,
    sample_format: format::Sample,
    decoder: &decoder::Audio,
) -> Result<filter::Graph, SetupResamplingFilterError> {
    let mut filter = filter::Graph::new();
    let input_spec = format!(
        "time_base={}:sample_rate={}:sample_fmt={}:channel_layout=0x{:x}",
        decoder.time_base(),
        decoder.rate(),
        decoder.format().name(),
        decoder.channel_layout().bits()
    );

    let filter_spec = format!("aresample={}", sample_rate);

    filter.add(&filter::find("abuffer").unwrap(), "in", &input_spec)?;
    filter.add(&filter::find("abuffersink").unwrap(), "out", "")?;

    {
        let mut out = filter.get("out").unwrap();

        out.set_sample_format(sample_format);
        out.set_channel_layout(ChannelLayout::STEREO);
        out.set_sample_rate(sample_rate);
    }

    filter
        .output("in", 0)?
        .input("out", 0)?
        .parse(&filter_spec)?;
    filter.validate()?;

    Ok(filter)
}

#[derive(Debug, thiserror::Error)]
pub enum SetupAudioEncoderError {
    #[error("Audio codec not found")]
    CodecNotFound,
    #[error("FFmpeg returned error: {0}")]
    FFmpegError(#[from] Error),
}

pub(crate) fn setup_audio_encoder(
    codec: &str,
    bit_rate: usize,
    sampling_rate: u32,
) -> Result<Encoder, SetupAudioEncoderError> {
    let ctx = codec::Context::new();
    let mut encoder = ctx.encoder().audio()?;

    encoder.set_bit_rate(bit_rate);
    encoder.set_rate(sampling_rate as i32);
    encoder.set_channel_layout(ChannelLayout::STEREO);
    encoder.set_format(match codec {
        "libmp3lame" => I16(Planar),
        "libfdk_aac" => I16(Packed),
        _ => return Err(SetupAudioEncoderError::CodecNotFound),
    });

    let codec = find_by_name(codec).ok_or_else(|| SetupAudioEncoderError::CodecNotFound)?;
    let encoder = encoder.open_as(codec)?;

    Ok(encoder)
}
