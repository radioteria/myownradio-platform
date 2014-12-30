package gemini.myownradio.flow;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.ff.FFDecoderBuilder;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 07.10.14.
 */
public class TrackPlayer implements AbstractPlayer {
    private final boolean jingled;
    private final OutputStream output;
    private final String file;
    private final ConcurrentBuffer broadcast;

    public TrackPlayer(ConcurrentBuffer broadcast, OutputStream output, String file, boolean jingled)
            throws FileNotFoundException {

        this.output = output;
        this.file = file;
        this.jingled = jingled;
        this.broadcast = broadcast;

    }

    public TrackPlayer(ConcurrentBuffer broadcast, OutputStream output, String file)
            throws FileNotFoundException {

        this(broadcast, output, file, false);
    }

    public void play() throws IOException {
        this.play(0);
    }

    public void play(int offset) throws IOException {

        ProcessBuilder pb;
        Process proc;

        pb = new ProcessBuilder(new FFDecoderBuilder(file, offset, jingled).generate());

        proc = pb.start();

        try (
                InputStream in = proc.getInputStream();
                InputStream err = proc.getErrorStream()
        ) {
            byte[] buffer = new byte[4096];
            int length;
            while ((length = in.read(buffer)) != -1) {
                output.write(buffer, 0, length);
                output.flush();

                // Clear error stream
                while (err.available() > 0) {
                    length = err.read(buffer);
                }

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

        proc.destroy();

    }
}
