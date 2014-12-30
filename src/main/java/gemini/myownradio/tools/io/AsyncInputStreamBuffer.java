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

    final private static int READ_BLOCK_SIZE = 4096;

    private InputStream source;
    private Thread thread;
    private ByteBuffer buffer;
    private int count;

    public AsyncInputStreamBuffer(InputStream source, int maximalSize) throws IOException {
        this.source = source;
        this.buffer = ByteBuffer.allocateDirect(maximalSize);
        this.count = 0;
        this.justReadInputStream();
    }

    private void justReadInputStream() throws IOException {

        thread = new Thread(() -> {

            byte[] data = new byte[READ_BLOCK_SIZE];
            int length;

            try (InputStream tmp = source) {
                while ((length = tmp.read(data)) != -1) {
                    synchronized (this) {
                        while (buffer.capacity() < length + count) {
                            wait();
                        }
                        count += length;
                        buffer.put(data, 0, length);
                        notify();
                    }
                }
                System.out.println("File buffering completed!");
            } catch (IOException | InterruptedException e) { /* NOP */ }

        });

        thread.start();

    }

    @Override
    public int read(byte[] b) throws IOException {
        return this.read(b, 0, b.length);
    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {

        try {
            synchronized (this) {
                while (count == 0) { wait(); }
                int length = (count > len) ? len : count;
                count -= length;
                buffer.position(0);
                buffer.get(b, 0, length);
                buffer.compact();
                buffer.position(count);
                notify();
                return length;
            }
        } catch (InterruptedException e) { /* NOP */ }

        return -1;

    }

    public int read() throws IOException {
        byte[] oneByte = new byte[1];
        int length = this.read(oneByte);
        if (length == 0) {
            throw new IOException();
        }
        return oneByte[0] & 0xFF;
    }

    @Override
    public void close() throws IOException {
        thread.interrupt();
    }

}
