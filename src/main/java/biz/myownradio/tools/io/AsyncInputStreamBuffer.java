package biz.myownradio.tools.io;

import biz.myownradio.tools.Logger;

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

    final public static int DEFAULT_BUFFER_SIZE = 16_777_216;

    private InputStream source;
    private Thread thread;
    private ByteBuffer buffer;
    private int count;

    private final Logger logger = new Logger(Logger.MessageKind.BUFFER);

    public AsyncInputStreamBuffer(InputStream source, int maximalSize) {
        this.source = source;
        this.buffer = ByteBuffer.allocateDirect(maximalSize);
        this.count = 0;
        this.justReadInputStream();
        logger.print("Initialized");
    }

    public AsyncInputStreamBuffer(InputStream source) {
        this(source, DEFAULT_BUFFER_SIZE);
    }

    private void justReadInputStream() {

        thread = new Thread(() -> {

            byte[] data = new byte[READ_BLOCK_SIZE];
            int length;

            try (InputStream tmp = source) {
                logger.print("Starting to read input stream");
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
                logger.printf("Input stream read completed! Remaining: %d bytes\n", count);
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
        return 0;
    }

    @Override
    public long skip(long n) throws IOException {
        System.out.println("SKIP!!!!!!!!!!!!!");
        return super.skip(n);
    }

    @Override
    public void close() throws IOException {
        thread.interrupt();
    }

}
