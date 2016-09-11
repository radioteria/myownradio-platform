package biz.myownradio.flow;

import biz.myownradio.ff.Encoder;

public enum AudioFormats {

    aacplus_24k (24000,  Encoder.AAC, "adts", "audio/aac", 1),
    aacplus_32k (32000,  Encoder.AAC, "adts", "audio/aac", 1),
    aacplus_64k (64000,  Encoder.AAC, "adts", "audio/aac", 1),
    aacplus_96k (96000,  Encoder.AAC, "adts", "audio/aac", 1),
    aacplus_128k(128000, Encoder.AAC, "adts", "audio/aac", 1),
    mp3_128k    (128000, Encoder.MP3, "mp3", "audio/mpeg", 1),
    mp3_192k    (192000, Encoder.MP3, "mp3", "audio/mpeg", 2),
    mp3_256k    (256000, Encoder.MP3, "mp3", "audio/mpeg", 2),
    mp3_320k    (320000, Encoder.MP3, "mp3", "audio/mpeg", 3);

    final int bitrate;
    final Encoder encoder;
    final String format;
    final String content;
    final int limitId;

    AudioFormats(int bitrate, Encoder encoder, String format, String content, int limitId) {
        this.bitrate = bitrate;
        this.encoder = encoder;
        this.format  = format;
        this.content = content;
        this.limitId = limitId;
    }

    public int getBitrate() {
        return bitrate;
    }

    public Encoder getEncoder() {
        return encoder;
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
