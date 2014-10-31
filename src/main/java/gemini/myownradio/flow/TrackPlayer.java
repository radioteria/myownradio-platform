package gemini.myownradio.flow;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.ff.FFDecoderBuilder;

import java.io.*;

/**
 * Created by Roman on 07.10.14.
 */
public class TrackPlayer implements AbstractPlayer {
    private final boolean jingled;
    private final OutputStream output;
    private final String filename;
    private final ConcurrentBuffer broadcast;

    public TrackPlayer(ConcurrentBuffer broadcast, OutputStream output, String filename, boolean jingled) throws FileNotFoundException {
        if (!new File(filename).exists()) {
            throw new FileNotFoundException("File '" + filename + "' not found!");
        }

        this.output = output;
        this.filename = filename;
        this.jingled = jingled;
        this.broadcast = broadcast;
    }

    public TrackPlayer(ConcurrentBuffer broadcast, OutputStream output, String filename) throws FileNotFoundException {
        this(broadcast, output, filename, false);
    }

    public void play() throws IOException {
        this.play(0);
    }

    public void play(int offset) throws IOException {

        ProcessBuilder pb;
        Process proc;

        pb = new ProcessBuilder(new FFDecoderBuilder(this.filename, offset, jingled).generate());

        try {
            proc = pb.start();
        } catch (IOException e) {
            System.err.println("Player couldn't start: " + e.getMessage());
            return;
        }

        try (
            InputStream in = proc.getInputStream();
            InputStream err = proc.getErrorStream();
        ) {
            byte[] buffer = new byte[4096];
            int length;
            while ((length = in.read(buffer)) != -1) {
                output.write(buffer, 0, length);
                // Clear error stream
                while (err.available() > 0) {
                    length = err.read(buffer);
                }

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

        proc.destroy();

    }
}
