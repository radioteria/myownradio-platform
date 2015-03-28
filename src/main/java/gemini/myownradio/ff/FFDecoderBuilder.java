package gemini.myownradio.ff;

import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;

/**
 * Created by Roman on 07.10.14.
 */
public class FFDecoderBuilder {
    private String filename;
    private int offset;
    private String[] cmd;

    public FFDecoderBuilder(int offset, boolean jingled) {
        this(null, offset, jingled);
    }

    public FFDecoderBuilder(String filename, int offset, boolean jingled) {
        this.filename = filename;
        this.offset = offset;

        DecimalFormatSymbols otherSymbols = new DecimalFormatSymbols();
        otherSymbols.setDecimalSeparator('.');

        DecimalFormat df = new DecimalFormat("0.###", otherSymbols);
        df.setGroupingUsed(false);

        cmd = jingled ?
                new String[]{
                        "ffmpeg",
                        "-hide_banner",
                        "-loglevel", "quiet",
                        "-ss", df.format((float) this.offset / 1_000F),
                        "-i", filename,
                        "-i", "ftp://morstorage:3bWdNNa0v@myownradio.biz/jingle.wav",
                        "-filter_complex", "[0:a]afade=t=in:st=1:d=3[a1],[a1]amix=inputs=2:duration=first:dropout_transition=3",
                        "-vn",
                        "-acodec", "pcm_s16le",
                        "-ar", "44100",
                        "-ac", "2",
                        "-f", "s16le",
                        "-"
                } :
                new String[]{
                        "ffmpeg",
                        "-hide_banner",
                        "-loglevel", "quiet",
                        "-ss", df.format((float) this.offset / 1_000F),
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
