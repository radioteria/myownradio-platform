package gemini.myownradio.flow;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.tools.io.NoiseInputStream;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 08.10.14.
 */
public class NoisePlayer implements AbstractPlayer {

    private final ConcurrentBuffer broadcast;
    private OutputStream output;

    public NoisePlayer(ConcurrentBuffer broadcast, OutputStream output) {
        this.broadcast = broadcast;
        this.output = output;
    }

    @Override
    public void play() throws IOException {

        try (
                InputStream in = new NoiseInputStream();
        ) {
            byte[] buffer = new byte[4096];
            int length;
            while ((length = in.read(buffer)) != -1) {
                output.write(buffer, 0, length);
                if (broadcast.isNotify()) {
                    broadcast.resetNotify();
                    break;
                }
            }
        } catch (IOException e) {
            if (e.getMessage().equals("Shutdown")) {
                throw new IOException(e);
            }
        }

    }

    @Override
    public void play(int offset) throws IOException {
        this.play();
    }
}
