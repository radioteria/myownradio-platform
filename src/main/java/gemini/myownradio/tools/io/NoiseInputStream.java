package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;

/**
 * Created by Roman on 08.10.14.
 */
public class NoiseInputStream extends InputStream {

    private Long length;

    public NoiseInputStream() {
        this(null);
    }

    public NoiseInputStream(Long length) {
        this.length = length;
    }

    @Override
    public int read() throws IOException {
        if (length != null && length-- <= 0L) return -1;
        return (int) (Math.random() * 255);
    }

    @Override
    public int read(byte[] b) throws IOException {
        return this.read(b, 0, b.length);
    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {
        if (length != null && length-- <= 0) return -1;
        if (length != null && length < len) len = length.intValue();

        for (int i = off; i < len; i++) {
            b[i] = (byte) (Math.random() * 255);
        }

        length -= len;

        return len;
    }

}
