package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;

/**
 * Created by Roman on 08.10.14.
 */
public class NoiseInputStream extends InputStream {

    @Override
    public int read() throws IOException {
        return (int) (Math.random() * 255);
    }

    @Override
    public int read(byte[] b) throws IOException {
        return this.read(b, 0, b.length);
    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {
        for (int i = off; i < len; i++) {
            b[i] = (byte) (Math.random() * 255);
        }
        return len;
    }
}
