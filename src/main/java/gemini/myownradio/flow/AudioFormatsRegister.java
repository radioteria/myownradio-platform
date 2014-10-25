package gemini.myownradio.flow;

import gemini.myownradio.ff.FFEncoderBuilder;

/**
 * Created by Roman on 07.10.14.
 */
public class AudioFormatsRegister {
    public static FFEncoderBuilder analyzeFormat(String format) {

        AudioFormats af;

        try {
            af = AudioFormats.valueOf(format);
        } catch (IllegalArgumentException e) {
            af = AudioFormats.mp3_128k;
        }

        return new FFEncoderBuilder(af);

    }
}
