#[derive(Debug, Hash, Eq, PartialEq, Clone)]
pub struct AudioFormat {
    pub bitrate: u16,
    pub format: &'static str,
    pub content_type: &'static str,
    pub codec: &'static str,
}

impl AudioFormat {
    pub const fn new(
        bitrate: u16,
        format: &'static str,
        content_type: &'static str,
        codec: &'static str,
    ) -> Self {
        AudioFormat {
            bitrate,
            format,
            content_type,
            codec,
        }
    }
}

impl ToString for AudioFormat {
    fn to_string(&self) -> String {
        format!("{}/{}k", self.format, self.bitrate)
    }
}

impl Default for AudioFormat {
    fn default() -> Self {
        AudioFormats::MP3_256K
    }
}

#[non_exhaustive]
pub struct AudioFormats;

impl AudioFormats {
    pub const MP3_128K: AudioFormat = AudioFormat::new(128, "mp3", "audio/mp3", "libmp3lame");
    pub const MP3_192K: AudioFormat = AudioFormat::new(192, "mp3", "audio/mp3", "libmp3lame");
    pub const MP3_256K: AudioFormat = AudioFormat::new(256, "mp3", "audio/mp3", "libmp3lame");
    pub const MP3_320K: AudioFormat = AudioFormat::new(320, "mp3", "audio/mp3", "libmp3lame");

    pub const AAC_PLUS_24K: AudioFormat = AudioFormat::new(24, "adts", "audio/aac", "libfdk_aac");
    pub const AAC_PLUS_32K: AudioFormat = AudioFormat::new(32, "adts", "audio/aac", "libfdk_aac");
    pub const AAC_PLUS_64K: AudioFormat = AudioFormat::new(64, "adts", "audio/aac", "libfdk_aac");
    pub const AAC_PLUS_96K: AudioFormat = AudioFormat::new(96, "adts", "audio/aac", "libfdk_aac");
    pub const AAC_PLUS_128K: AudioFormat = AudioFormat::new(128, "adts", "audio/aac", "libfdk_aac");

    pub fn from_string(format: &str) -> Option<AudioFormat> {
        match format {
            "mp3_128k" => Some(AudioFormats::MP3_128K),
            "mp3_192k" => Some(AudioFormats::MP3_192K),
            "mp3_256k" => Some(AudioFormats::MP3_256K),
            "mp3_320k" => Some(AudioFormats::MP3_320K),

            "aacplus_24k" => Some(AudioFormats::AAC_PLUS_24K),
            "aacplus_32k" => Some(AudioFormats::AAC_PLUS_32K),
            "aacplus_64k" => Some(AudioFormats::AAC_PLUS_64K),
            "aacplus_96k" => Some(AudioFormats::AAC_PLUS_96K),
            "aacplus_128k" => Some(AudioFormats::AAC_PLUS_128K),

            _ => None,
        }
    }
}
