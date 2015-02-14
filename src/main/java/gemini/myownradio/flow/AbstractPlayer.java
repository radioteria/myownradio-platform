package gemini.myownradio.flow;

import gemini.myownradio.exception.DecoderException;

import java.io.IOException;

/**
 * Created by Roman on 07.10.14.
 */
public interface AbstractPlayer {
    public void play() throws IOException, DecoderException;

    public void play(int offset) throws IOException, DecoderException;
}
