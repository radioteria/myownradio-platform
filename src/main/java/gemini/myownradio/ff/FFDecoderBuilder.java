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

        if (jingled) {
            cmd = new String[]{
                    "ffmpeg",
                    "-ss", new DecimalFormat("0.###").format((float) this.offset / 1000F),
                    "-i", this.filename,
                    "-i", "/media/www/myownradio.biz/jingle.wav",
                    "-filter_complex", "[0:a]afade=t=in:ss=0:st=1:d=3[a1],[a1]amix=inputs=2:duration=first:dropout_transition=3",
                    "-acodec", "pcm_s16le",
                    "-ar", "44100",
                    "-ac", "2",
                    "-f", "s16le",
                    "-"
            };
        } else {
            cmd = new String[]{
                    "ffmpeg",
                    "-ss", new DecimalFormat("0.###").format((float) this.offset / 1000F),
                    "-i", this.filename,
                    "-filter", "afade=t=in:ss=0:st=0:d=1",
                    "-acodec", "pcm_s16le",
                    "-ar", "44100",
                    "-ac", "2",
                    "-f", "s16le",
                    "-"
            };
        }
    }

    public String[] generate() {
        //System.out.println(Arrays.toString(cmd));
        return cmd;
    }

    public String getFilename() {
        return filename;
    }

    public int getOffset() {
        return offset;
    }
}
