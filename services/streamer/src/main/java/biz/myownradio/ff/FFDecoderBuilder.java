package biz.myownradio.ff;

import biz.myownradio.tools.MORSettings;

import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;
import java.util.Arrays;
import java.util.List;

/**
 * Created by Roman on 07.10.14
 */
public class FFDecoderBuilder {

    final private String[] cmd;

    public FFDecoderBuilder(String filename, int offset, boolean playJingle) {

        List<String> builder = Helper.getFFmpegPrefix();

        DecimalFormatSymbols otherSymbols = new DecimalFormatSymbols();
        otherSymbols.setDecimalSeparator('.');

        DecimalFormat df = new DecimalFormat("0.###", otherSymbols);
        df.setGroupingUsed(false);

        builder.addAll(Arrays.asList(
                "-fflags", "fastseek",
                "-ss", df.format((float) offset / 1_000F),
                "-i", filename
        ));

        if (playJingle) {
            int jingleDelay = MORSettings.getIntegerNow("defaults.player.jingle.delay");
            builder.addAll(Arrays.asList(
                    "-i", MORSettings.getStringNow("defaults.player.jingle.url"),
                    "-filter_complex", buildComplexFilter(jingleDelay)
            ));
        } else {
            builder.addAll(Arrays.asList(
                    "-filter", "afade=t=in:st=0:d=1"
            ));
        }

        builder.addAll(Arrays.asList(
                "-acodec", "pcm_s16le",
                "-ar", "44100",
                "-ac", "2",
                "-f", "s16le",
                "-strict", "-2",
                "-"
        ));

        this.cmd = builder.toArray(new String[builder.size()]);

    }

    private static String buildComplexFilter(int delay) {

        return "[0:a]afade=t=in:st=" + delay + ":d=3[a1],[a1]amix=inputs=2:duration=first:dropout_transition=3";

    }

    public String[] getCommand() {

        return cmd;

    }
}
