use ffmpeg_next::ChannelLayout;

mod decoder;
mod utils;

pub(crate) use decoder::{decode_audio_file, AudioFileDecodeError};

const INTERNAL_CHANNEL_LAYOUT: ChannelLayout = ChannelLayout::STEREO;
