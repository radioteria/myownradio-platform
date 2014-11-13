package gemini.myownradio.tools.io;

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

    protected int sequence = 0;
    protected PipeIO pipe;

    public ThroughOutputStream(OutputStream out, String[] cmd) throws IOException {
        this(out, null, cmd);
    }

    public ThroughOutputStream(OutputStream out, OutputStream errOut, String[] cmd) throws IOException {
        super(out);

        ProcessBuilder pb = new ProcessBuilder(cmd);

        proc = pb.start();

        pipe = new PipeIO(proc.getInputStream(), this.out);

        this.err = proc.getErrorStream();
        this.os = proc.getOutputStream();

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
            len = err.read(buffer);
            if (errOut != null) {
                errOut.write(buffer, 0, len);
            }
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
        os.write(b, off, len);
        checkInput();
        readError();
    }

    @Override
    public void flush() throws IOException {
        os.flush();
    }

    @Override
    public void close() throws IOException {
        proc.destroy();
        pipe.thread().interrupt();
        try {
            pipe.thread().join();
        } catch (InterruptedException e) {/*NOP*/}
    }
}
