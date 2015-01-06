package gemini.myownradio.flow;

/**
 * Created by Roman on 14.10.14.
 */
public enum AudioFormats {
    aacplus_24k(24000, "libfdk_aac", "adts", "audio/aac"),
    aacplus_32k(32000, "libfdk_aac", "adts", "audio/aac"),
    aacplus_64k(64000, "libfdk_aac", "adts", "audio/aac"),
    aacplus_96k(96000, "libfdk_aac", "adts", "audio/aac"),
    aacplus_128k(128000, "libfdk_aac", "adts", "video/x-flv"),
    mp3_128k(128000, "libmp3lame", "mp3", "audio/mpeg"),
    mp3_256k(256000, "libmp3lame", "mp3", "audio/mpeg");

    final int bitrate;
    final String codec;
    final String format;
    final String content;

    AudioFormats(int bitrate, String codec, String format, String content) {
        this.bitrate = bitrate;
        this.codec = codec;
        this.format = format;
        this.content = content;
    }

    public int getBitrate() {
        return bitrate;
    }

    public String getCodec() {
        return codec;
    }

    public String getFormat() {
        return format;
    }

    public String getContent() {
        return content;
    }

}
