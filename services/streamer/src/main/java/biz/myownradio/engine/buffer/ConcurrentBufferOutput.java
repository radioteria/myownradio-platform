package biz.myownradio.engine.buffer;

import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14.
 *
 * OutputStream layer for audio flow generator
 */
public class ConcurrentBufferOutput extends OutputStream {

    private ConcurrentBufferUnit me;

    public ConcurrentBufferOutput(ConcurrentBufferUnit me) {
        this.me = me;
    }

    @Override
    public void write(int b) throws IOException {
        if (b < 0 || b > 255) {
            throw new IllegalArgumentException("Wrong integer value");
        }

        byte[] tmp = {(byte) b};

        this.write(tmp);
    }

    @Override
    public void write(byte[] b) throws IOException {
        me.write(b);
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {

        byte[] tmp = new byte[len];

        System.arraycopy(b, off, tmp, 0, len);

        this.write(tmp);
    }

    @Override
    public void flush() throws IOException {
        super.flush();
    }

    @Override
    public void close() throws IOException {
        super.close();
    }

}
