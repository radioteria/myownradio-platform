package gemini.myownradio.ff;

import gemini.myownradio.flow.AudioFormats;

/**
 * Created by Roman on 07.10.14.
 */
public class FFEncoderBuilder {

    final private AudioFormats format;

    private String[] cmd;

    public FFEncoderBuilder(AudioFormats afs) {
        this.format = afs;
        this.prepare();
    }

    private void prepare() {
        switch (format.getCodec()) {
            case "libfdk_aac":
                cmd = new String[]{
                        "ffmpeg",
                        "-hide_banner",
//                        "-loglevel", "quiet",
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
                        "-acodec", format.getCodec(),
                        "-profile:a", "aac_he_v2",
                        "-strict", "-2",
                        "-bufsize", "1024k",
                        "-f", format.getFormat(),
                        "-"
                };
                break;
            default:
                cmd = new String[]{
                        "ffmpeg",
                        "-hide_banner",
//                        "-loglevel", "quiet",
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
                        "-acodec", format.getCodec(),
                        "-strict", "-2",
                        "-bufsize", "1024k",
                        "-f", format.getFormat(),
                        "-"
                };

        }
    }

    public String[] generate() {
        return cmd;
    }

    public AudioFormats getAudioFormat() {
        return format;
    }

    public String getAudioFormatName() {
        return format.name();
    }
}
