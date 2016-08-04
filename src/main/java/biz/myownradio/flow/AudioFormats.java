package biz.myownradio.flow;

/**
 * Created by Roman on 14.10.14.
 */
public enum AudioFormats {

    aacplus_24k (24000,  "libfdk_aac", "adts", "audio/aac", 1),
    aacplus_32k (32000,  "libfdk_aac", "adts", "audio/aac", 1),
    aacplus_64k (64000,  "libfdk_aac", "adts", "audio/aac", 1),
    aacplus_96k (96000,  "libfdk_aac", "adts", "audio/aac", 1),
    aacplus_128k(128000, "libfdk_aac", "adts", "audio/aac", 1),
    mp3_128k    (128000, "libmp3lame", "mp3", "audio/mpeg", 1),
    mp3_192k    (192000, "libmp3lame", "mp3", "audio/mpeg", 2),
    mp3_256k    (256000, "libmp3lame", "mp3", "audio/mpeg", 2),
    mp3_320k    (320000, "libmp3lame", "mp3", "audio/mpeg", 3);

    final int bitrate;
    final String codec;
    final String format;
    final String content;
    final int limitId;

    AudioFormats(int bitrate, String codec, String format, String content, int limitId) {
        this.bitrate = bitrate;
        this.codec = codec;
        this.format = format;
        this.content = content;
        this.limitId = limitId;
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

    public int getLimitId() {
        return limitId;
    }
}
