package biz.myownradio.tools.io;

import biz.myownradio.tools.Logger;

import java.io.*;

/**
 * Created by Roman on 09.10.14.
 */
public class ThroughOutputStream extends FilterOutputStream implements Closeable {

    protected InputStream in;
    protected InputStream err;
    protected OutputStream os;

    protected OutputStream errOut;

    protected Process proc;

    protected PipeIO pipe;

    static Logger logger = new Logger(Logger.MessageKind.PLAYER);

    public ThroughOutputStream(OutputStream out, String[] cmd) throws IOException {
        this(out, null, cmd);
    }

    public ThroughOutputStream(OutputStream out, OutputStream errOut, String[] cmd) throws IOException {

        super(out);

        ProcessBuilder pb = new ProcessBuilder(cmd);

        proc = pb.start();

        this.err = proc.getErrorStream();
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

    private void readError() throws IOException {
        byte[] buffer = new byte[4096];
        int len;
        while (err.available() > 0) {
            len = err.read(buffer, 0, Math.min(err.available(), buffer.length));
            logger.print(new String(buffer, 0, len));
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
        readError();
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
                logger.print("STREAMER SUCCESSFULLY INTERRUPTED");
            } catch (InterruptedException e) {
                logger.print("STREAMER STOP INTERRUPTED");
            }
        }

    }
}
