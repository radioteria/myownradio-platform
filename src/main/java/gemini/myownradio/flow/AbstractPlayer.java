package gemini.myownradio.flow;

import java.io.IOException;

/**
 * Created by Roman on 07.10.14.
 */
public interface AbstractPlayer {
    public void play() throws IOException;
    public void play(int offset) throws IOException;
}
