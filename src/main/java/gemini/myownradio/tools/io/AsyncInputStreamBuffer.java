package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;
import java.nio.ByteBuffer;

/**
 * Created by Roman on 29.12.2014.
 * <p>
 * Asynchronous InputStream buffer
 */
public class AsyncInputStreamBuffer extends InputStream {

    private final static int READ_BLOCK_SIZE = 4096;

    private InputStream source;

    private Thread thread;

    private ByteBuffer buffer;

    public AsyncInputStreamBuffer(InputStream source, int maximalSize) throws IOException {
        this.source = source;
        this.buffer = ByteBuffer.allocateDirect(maximalSize);

        this.justRead();
    }

    private void justRead() throws IOException {

        thread = new Thread(() -> {

            byte[] data = new byte[READ_BLOCK_SIZE];
            int length;

            try (InputStream tmp = source) {

                while ((length = tmp.read(data)) != 0) {
                    synchronized (this) {
                        while (buffer.remaining() < length) {
                            wait();
                        }
                        buffer.put(data);
                    }
                }

            } catch (IOException | InterruptedException e) { /* NOP */ }

        });

        thread.start();

    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {
        return super.read(b, off, len);
    }

    public int read() {
        return 0;
    }

}
