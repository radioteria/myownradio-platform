package biz.myownradio.ff;

import biz.myownradio.exception.DecoderException;
import biz.myownradio.flow.AudioFormats;

import java.util.Arrays;
import java.util.List;

/**
 * Created by Roman on 07.10.14
 */
public class FFEncoderBuilder {

    final private AudioFormats format;

    private String[] cmd;

    public FFEncoderBuilder(AudioFormats afs) {
        this.format = afs;
        this.prepare();
    }

    private void prepare() {

        List<String> builder = Helper.getFFmpegPrefix();

        builder.addAll(Arrays.asList(
            "-hide_banner",
            "-loglevel", "quiet",
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
            "-ab", Integer.toString(format.getBitrate())
        ));

        switch (format.getEncoder()) {
            case AAC:
                builder.addAll(Arrays.asList(
                    "-acodec", "libfdk_aac",
                    "-profile:a", "aac_he_v2"
                ));
                break;
            case MP3:
                builder.addAll(Arrays.asList(
                    "-acodec", format.getEncoder().getCodecName()
                ));
                break;
        }

        builder.addAll(Arrays.asList(
            "-strict", "-2",
            "-f", format.getFormat(),
            "-"
        ));

        cmd = (String[]) builder.toArray();

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
