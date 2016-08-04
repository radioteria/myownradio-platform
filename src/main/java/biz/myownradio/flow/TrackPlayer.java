package biz.myownradio.flow;

import biz.myownradio.engine.buffer.ConcurrentBuffer;
import biz.myownradio.exception.DecoderException;
import biz.myownradio.ff.FFDecoderBuilder;
import biz.myownradio.tools.MORLogger;
import biz.myownradio.tools.MORSettings;
import biz.myownradio.tools.io.PipeIO;

import java.io.*;

/**
 * Created by Roman on 07.10.14.
 */
public class TrackPlayer implements AbstractPlayer {
    private final boolean jingled;
    private final OutputStream output;
    private final String file;
    private final ConcurrentBuffer broadcast;

    private static final Object lock = new Object();

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

        String[] command = new FFDecoderBuilder(file, offset, jingled).getCommand();

        String decoderLogFile = MORSettings.getString("server.logdir").orElse("/tmp") +
                "/decoder_" + Thread.currentThread().getName() + "_" + System.currentTimeMillis() +".log";

        ProcessBuilder pb;
        final Process process;

        int bytesDecoded = 0;

        logger.println("Initializing process builder...");

        pb = new ProcessBuilder(command);
        pb.redirectError(new File(decoderLogFile));

        synchronized (lock) {
            process = pb.start();
            logger.println("Initialization done");
        }

        PipeIO pipeIO;

        try (
                InputStream in = process.getInputStream();
                //OutputStream out = process.getOutputStream();
                //InputStream url = new URL(this.file).openStream()
        ) {
            //pipeIO = new PipeIO(url, out, true);

            byte[] buffer = new byte[4096];
            int length;
            logger.println("[START]");
            while ((length = in.read(buffer)) != -1) {
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
            try {
                process.destroyForcibly().waitFor();
            } catch (InterruptedException ie) { /* NOP */ }
        }

        int exitStatus = process.exitValue();

        logger.sprintf("Exit value: %d", exitStatus);
        logger.sprintf("Bytes decoded: %d", bytesDecoded);

        if (bytesDecoded == 0) {
            throw new DecoderException();
        }

    }
}
