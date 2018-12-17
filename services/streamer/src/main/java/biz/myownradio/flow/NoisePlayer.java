package biz.myownradio.flow;

import biz.myownradio.engine.buffer.ConcurrentBuffer;
import biz.myownradio.tools.io.NoiseInputStream;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 08.10.14.
 */
public class NoisePlayer implements AbstractPlayer {

    private final ConcurrentBuffer broadcast;
    private OutputStream output;
    private Long length;

    final private static int PCM_BYTE_RATE = 176400;

    public NoisePlayer(ConcurrentBuffer broadcast, OutputStream output) {
        this(broadcast, output, null);
    }

    public NoisePlayer(ConcurrentBuffer broadcast, OutputStream output, Long length) {
        this.broadcast = broadcast;
        this.output = output;
        this.length = length;
    }

    @Override
    public void play() throws IOException {

        try (
                InputStream in = new NoiseInputStream(length != null ? length * PCM_BYTE_RATE : null);
        ) {
            byte[] buffer = new byte[4096];
            int length;
            while ((length = in.read(buffer)) != -1) {
                output.write(buffer, 0, length);
                if (broadcast.isNotified()) {
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
