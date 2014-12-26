package gemini.myownradio.tools;

import java.io.IOException;
import java.nio.ByteBuffer;

/**
 * Created by Roman on 26.12.2014.
 */
public class CircularByteBuffer {

    private long count;
    private ByteBuffer buffer;
    private int length;

    // Default read timeout is 5 seconds.
    private final static long DEFAULT_TIMEOUT = 5_000L;

    private long timeout;

    public CircularByteBuffer(int size, long timeout) {
        this.count = 0L;
        this.buffer = ByteBuffer.allocateDirect(size);
        this.length = size;
        this.timeout = timeout;
    }

    public CircularByteBuffer(int size) {
        this(size, DEFAULT_TIMEOUT);
    }

    public void putBytes(byte[] b, int pos, int len) {

        buffer.position(len);
        buffer.compact();
        buffer.put(b, pos, len);
        count =+ len;

        synchronized (this) {
            this.notifyAll();
        }

    }

    public void putBytes(byte[] b) {
        this.putBytes(b, 0, b.length);
    }

    public long getPosition() {
        return count;
    }

    public int getLength() {
        return length;
    }

    public long read(long after, byte[] b, int len) throws IOException {

        // Will read buffer contents which written after specified position.
        // In case if no data written after specified position method will
        // be blocked until new data arrive.

        long threshold = System.currentTimeMillis() + timeout;

        while (threshold > System.currentTimeMillis()) {

            if (count < after) {

                synchronized (this) {
                    try { this.wait(timeout); }
                    catch (InterruptedException cannotHappen) { /* NOP */ }
                }

            } else {

                int newBytes = (int) (count - after);

                buffer.rewind();

                if (newBytes > len) {
                    buffer.position(length - len);
                    buffer.get(b, 0, len);
                    return count + len;
                } else {
                    buffer.position(length - newBytes);
                    buffer.get(b, 0, newBytes);
                    return count + newBytes;
                }

            }

        }

        throw new IOException("Buffer wait timeout");

    }

}
