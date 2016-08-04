package biz.myownradio.flow;

import biz.myownradio.ff.FFEncoderBuilder;
import biz.myownradio.tools.MORLogger;

/**
 * Created by Roman on 07.10.14.
 */
public class AudioFormatsRegister {

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);

    public static FFEncoderBuilder analyzeFormat(String format, int limitId) {

        AudioFormats af;

        try {
            af = AudioFormats.valueOf(format);
            if (af.getLimitId() > limitId) {
                throw new IllegalArgumentException("User has no permission to access stream in this quality: " + af.toString());
            }
        } catch (IllegalArgumentException e) {
            af = AudioFormats.mp3_128k;
            logger.exception(e);
        }

        return new FFEncoderBuilder(af);

    }
}
