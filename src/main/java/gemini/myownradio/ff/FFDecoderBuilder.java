package gemini.myownradio.ff;

import java.text.DecimalFormat;

/**
 * Created by Roman on 07.10.14.
 */
public class FFDecoderBuilder {
    private String filename;
    private int offset;
    private String[] cmd;

    public FFDecoderBuilder(String filename, int offset, boolean jingled) {
        this.filename = filename;
        this.offset = offset;

        cmd = jingled ?
                new String[]{
                        "nohup",
                        "ffmpeg",
                        "-hide_banner",
//                        "-loglevel", "quiet",
                        "-err_detect", "explode",
                        "-ss", new DecimalFormat("0.###").format((float) this.offset / 1_000F),
                        "-i", filename,
                        "-i", "/media/www/myownradio.biz/jingle.wav",
                        "-filter_complex", "[0:a]afade=t=in:st=1:d=3[a1],[a1]amix=inputs=2:duration=first:dropout_transition=3",
                        "-vn",
                        "-acodec", "pcm_s16le",
                        "-ar", "44100",
                        "-ac", "2",
                        "-f", "s16le",
                        "-"
                } :
                new String[]{
                        "nohup",
                        "ffmpeg",
                        "-hide_banner",
//                        "-loglevel", "quiet",
                        "-err_detect", "explode",
                        "-ss", new DecimalFormat("0.###").format((float) this.offset / 1_000F),
                        "-i", filename,
                        "-filter", "afade=t=in:st=0:d=1",
                        "-vn",
                        "-acodec", "pcm_s16le",
                        "-ar", "44100",
                        "-ac", "2",
                        "-f", "s16le",
                        "-"
                };
    }

    public String[] generate() {
        return cmd;
    }

    public int getOffset() {
        return offset;
    }
}
