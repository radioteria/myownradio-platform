package gemini.myownradio.flow;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.exception.DecoderException;
import gemini.myownradio.ff.FFDecoderBuilder;
import gemini.myownradio.tools.MORLogger;
import gemini.myownradio.tools.io.PipeIO;

import java.io.*;

/**
 * Created by Roman on 07.10.14.
 */
public class TrackPlayer implements AbstractPlayer {
    private final boolean jingled;
    private final OutputStream output;
    private final String file;
    private final ConcurrentBuffer broadcast;

    MORLogger logger = new MORLogger(MORLogger.MessageKind.PLAYER);

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

    public void play() throws IOException, DecoderException {
        this.play(0);
    }

    public void play(int offset) throws IOException, DecoderException {

        ProcessBuilder pb;
        final Process process;
        PipeIO pipe;

        int bytesDecoded = 0;

        logger.println("Initializing process builder...");

        pb = new ProcessBuilder(new FFDecoderBuilder(offset, jingled).generate());

        pb.redirectError(new File("/tmp/decode_" + Thread.currentThread().getName() + ".log"));

        logger.println("Starting process builder...");

        try {
            process = pb.start();
        } catch (IOException exception) {
            logger.println("Process could not start");
            throw exception;
        }

        logger.println("Getting streams...");

        pipe = new PipeIO(new FileInputStream(new File(file)), process.getOutputStream(), false);

        try (InputStream in = process.getInputStream()) {
            byte[] buffer = new byte[4096];
            int length, available;
            logger.println("[START]");
            while ((length = in.read(buffer)) != 0) {
                bytesDecoded += length;
                output.write(buffer, 0, length);
                output.flush();
                if (broadcast.isNotified()) {
                    broadcast.resetNotify();
                    break;
                }
            }
            logger.println("[DONE]");
        } catch (IOException e) {
            logger.println("[EXCEPTION]");
            if (e.getMessage().equals("Shutdown")) {
                throw new IOException(e);
            }
        } finally {
            logger.println("[FINALLY]");
            try { process.destroyForcibly().waitFor(); }
            catch (InterruptedException ie) { /* NOP */ }
        }

        int exitStatus = process.exitValue();

        logger.sprintf("Exit value: %d", exitStatus);
        logger.sprintf("Bytes decoded: %d", bytesDecoded);

        if (bytesDecoded == 0) {
            throw new DecoderException();
        }

    }
}
