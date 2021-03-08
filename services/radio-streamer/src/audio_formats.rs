pub enum AudioFormat {
    MP3_128k,
    MP3_192k,
    MP3_256k,
    MP3_320k,
    AacPlus24k,
    AacPlus32k,
    AacPlus64k,
    AacPlus96k,
    AacPlus128k,
}

impl AudioFormat {
    pub fn from_string(format: &str) -> Option<Self> {
        match format {
            "mp3_128k" => Some(AudioFormat::MP3_128k),
            "mp3_192k" => Some(AudioFormat::MP3_192k),
            "mp3_256k" => Some(AudioFormat::MP3_256k),
            "mp3_320k" => Some(AudioFormat::MP3_320k),
            // @todo Unblock after build ffmpeg with libfdk_aac codec
            // "aacplus_24k" => Some(AudioFormat::AacPlus24k),
            // "aacplus_32k" => Some(AudioFormat::AacPlus32k),
            // "aacplus_64k" => Some(AudioFormat::AacPlus64k),
            // "aacplus_96k" => Some(AudioFormat::AacPlus96k),
            // "aacplus_128k" => Some(AudioFormat::AacPlus128k),
            _ => None,
        }
    }

    pub fn bitrate(&self) -> u16 {
        match *self {
            AudioFormat::MP3_128k => 128,
            AudioFormat::MP3_192k => 192,
            AudioFormat::MP3_256k => 256,
            AudioFormat::MP3_320k => 320,

            AudioFormat::AacPlus24k => 24,
            AudioFormat::AacPlus32k => 32,
            AudioFormat::AacPlus64k => 64,
            AudioFormat::AacPlus96k => 96,
            AudioFormat::AacPlus128k => 128,
        }
    }

    pub fn format(&self) -> String {
        match *self {
            AudioFormat::MP3_128k
            | AudioFormat::MP3_192k
            | AudioFormat::MP3_256k
            | AudioFormat::MP3_320k => "mp3".to_string(),

            AudioFormat::AacPlus24k
            | AudioFormat::AacPlus32k
            | AudioFormat::AacPlus64k
            | AudioFormat::AacPlus96k
            | AudioFormat::AacPlus128k => "adts".to_string(),
        }
    }

    pub fn content_type(&self) -> String {
        match *self {
            AudioFormat::MP3_128k
            | AudioFormat::MP3_192k
            | AudioFormat::MP3_256k
            | AudioFormat::MP3_320k => "audio/mp3".to_string(),

            AudioFormat::AacPlus24k
            | AudioFormat::AacPlus32k
            | AudioFormat::AacPlus64k
            | AudioFormat::AacPlus96k
            | AudioFormat::AacPlus128k => "audio/aac".to_string(),
        }
    }

    pub fn codec(&self) -> String {
        match *self {
            AudioFormat::MP3_128k
            | AudioFormat::MP3_192k
            | AudioFormat::MP3_256k
            | AudioFormat::MP3_320k => "libmp3lame".to_string(),

            AudioFormat::AacPlus24k
            | AudioFormat::AacPlus32k
            | AudioFormat::AacPlus64k
            | AudioFormat::AacPlus96k
            | AudioFormat::AacPlus128k => "libfdk_aac".to_string(),
        }
    }
}
