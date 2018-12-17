package biz.myownradio.tools.io;

import biz.myownradio.tools.MORLogger;
import biz.myownradio.tools.MORSettings;

import java.io.*;

/**
 * Created by Roman on 09.10.14.
 */
public class ThroughOutputStream extends FilterOutputStream implements Closeable {

    protected InputStream in;
    protected OutputStream os;

    protected OutputStream errOut;

    protected Process proc;

    protected PipeIO pipe;

    static MORLogger logger = new MORLogger(MORLogger.MessageKind.PLAYER);

    public ThroughOutputStream(OutputStream out, String[] cmd) throws IOException {
        this(out, null, cmd);
    }

    public ThroughOutputStream(OutputStream out, OutputStream errOut, String[] cmd) throws IOException {

        super(out);

        ProcessBuilder pb = new ProcessBuilder(cmd);

        String encoderLogFile = MORSettings.getString("server.log.dir").orElse("/tmp") +
                "/encoder_" + Thread.currentThread().getName() + "_" + System.currentTimeMillis() +".log";

        pb.redirectError(new File(encoderLogFile));

        proc = pb.start();

        this.os = proc.getOutputStream();
        this.in = proc.getInputStream();

        pipe = new PipeIO(this.in, this.out);

        this.errOut = errOut;

    }

    private void checkInput() throws IOException {
        if (pipe.isThrowed()) {
            throw pipe.getException();
        }
    }

    byte[] oneByte = new byte[1];

    @Override
    public void write(int b) throws IOException {
        oneByte[0] = (byte) b;
        this.write(oneByte, 0, oneByte.length);
    }

    @Override
    public void write(byte[] b) throws IOException {
        this.write(b, 0, b.length);
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {
        checkInput();
        os.write(b, off, len);
    }

    @Override
    public void flush() throws IOException {
        os.flush();
    }

    @Override
    public void close() throws IOException {

        try (InputStream inputStream = this.in; OutputStream outputStream = this.os;
             OutputStream outputStream1 = this.errOut) {
            /* Nothing to do. Just close. */
        } finally {

            Process pr = proc.destroyForcibly();
            pipe.thread().interrupt();

            try {
                pipe.thread().join();
                pr.waitFor();
                logger.println("STREAMER SUCCESSFULLY INTERRUPTED");
            } catch (InterruptedException e) {
                logger.println("STREAMER STOP INTERRUPTED");
            }
        }

    }
}
