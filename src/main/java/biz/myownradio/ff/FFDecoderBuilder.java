package biz.myownradio.ff;

import biz.myownradio.tools.MORSettings;

import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;

/**
 * Created by Roman on 07.10.14
 */
public class FFDecoderBuilder {
    private String[] cmd;

    public FFDecoderBuilder(String filename, int offset, boolean jingled) {
        DecimalFormatSymbols otherSymbols = new DecimalFormatSymbols();
        otherSymbols.setDecimalSeparator('.');

        DecimalFormat df = new DecimalFormat("0.###", otherSymbols);
        df.setGroupingUsed(false);

        cmd = jingled ?
                new String[]{
                        MORSettings.getString("command.ffmpeg").orElse("ffmpeg"),
                        "-fflags", "nobuffer",
                       // "-re",
//                        "-loglevel", "quiet",
                        "-ss", df.format((float) offset / 1_000F),
                        "-i", filename,
                        "-i", "http://myownradio.biz/jingle.wav",
                        "-filter_complex", "[0:a]afade=t=in:st=1:d=3[a1],[a1]amix=inputs=2:duration=first:dropout_transition=3",
                        "-vn",
                        "-acodec", "pcm_s16le",
                        "-ar", "44100",
                        "-ac", "2",
                        "-f", "s16le",
                        "-strict", "-2",
                        "-"
                } :
                new String[]{
                        MORSettings.getString("command.ffmpeg").orElse("ffmpeg"),
                        "-fflags", "nobuffer",
                       // "-re",
//                        "-loglevel", "quiet",
                        "-ss", df.format((float) offset / 1_000F),
                        "-i", filename,
                        "-filter", "afade=t=in:st=0:d=1",
                        "-vn",
                        "-acodec", "pcm_s16le",
                        "-ar", "44100",
                        "-ac", "2",
                        "-f", "s16le",
                        "-strict", "-2",
                        "-"
                };
    }

    public String[] getCommand() {
        return cmd;
    }
}
