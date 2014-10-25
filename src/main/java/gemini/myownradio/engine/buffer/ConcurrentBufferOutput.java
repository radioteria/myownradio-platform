package gemini.myownradio.engine.buffer;

import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14.
 */
public class ConcurrentBufferOutput extends OutputStream {

    private ConcurrentBufferCreator me;

    public ConcurrentBufferOutput(ConcurrentBufferCreator me) {
        this.me = me;
    }

    @Override
    public void write(int b) throws IOException {
        if (b < 0 || b > 255) {
            throw new IllegalArgumentException("Wrong integer value");
        }

        byte[] tmp = {(byte)b};

        this.write(tmp);
    }

    @Override
    public void write(byte[] b) throws IOException {
        this.write(b, 0, b.length);
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {
        me.write(b, off, len);
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
