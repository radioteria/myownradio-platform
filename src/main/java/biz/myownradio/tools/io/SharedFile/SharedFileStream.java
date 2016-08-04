package biz.myownradio.tools.io.SharedFile;

import java.io.IOException;
import java.io.InputStream;

/**
 * Created by roman on 13.03.15.
 */
public class SharedFileStream extends InputStream {

    private final SharedFile file;

    private long offset = 0;

    public SharedFileStream(SharedFile file) throws IOException {
        this.file = file;
        this.file.increaseConsumers();
    }

    @Override
    public int read(byte[] b) throws IOException {
        return read(b, 0, b.length);
    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {
        /* Do magic here */
        int count = this.file.readOffset(offset, b, off, len);
        this.offset += count;
        return count;
    }

    @Override
    public long skip(long n) throws IOException {
        if (this.offset + n > this.file.length()) {
            throw new IOException("Seek position is greater than file length");
        }
        return this.offset += n;
    }

    @Override
    public void close() throws IOException {
        this.file.decreaseConsumers();
        this.file.checkAndClose();
    }

    @Override
    public int read() throws IOException {
        throw new UnsupportedOperationException();
    }
}