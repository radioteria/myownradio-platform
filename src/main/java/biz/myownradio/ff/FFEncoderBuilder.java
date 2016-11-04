package biz.myownradio.ff;

import biz.myownradio.flow.AudioFormats;

import java.util.Arrays;
import java.util.List;

/**
 * Created by Roman on 07.10.14
 */
public class FFEncoderBuilder {

    private AudioFormats format;

    private String[] cmd;

    public FFEncoderBuilder(AudioFormats afs) {
        this.format = afs;
        this.prepare();
    }

    public String toString() {
        return format.toString();
    }

    private void prepare() {

        List<String> builder = Helper.getFFmpegPrefix();

        builder.addAll(Arrays.asList(
//            "-hide_banner",
//            "-loglevel", "quiet",
            "-acodec", "pcm_s16le",
            "-ar", "44100",
            "-ac", "2",
            "-f", "s16le",
            "-i", "-",
            "-af", "compand=0 0:1 1:-90/-900 -70/-70 -21/-21 0/-15:0.01:12:0:0",
            "-map_metadata", "-1",
            "-vn",
            "-ar", "44100",
            "-ac", "2",
            "-ab", Integer.toString(format.getBitrate()),
            "-acodec", format.getEncoder().getEncoderName()
        ));

        switch (format.getEncoder()) {
            case AAC:
                builder.addAll(Arrays.asList(
                    "-profile:a", "aac_he_v2"
                ));
                break;
        }

        builder.addAll(Arrays.asList(
            "-strict", "-2",
            "-f", format.getFormat(),
            "-"
        ));

        cmd = builder.toArray(new String[0]);

    }

    public String[] getCommand() {
        return cmd;
    }

    public AudioFormats getAudioFormat() {
        return format;
    }

    public String getAudioFormatName() {
        return format.name();
    }
}
