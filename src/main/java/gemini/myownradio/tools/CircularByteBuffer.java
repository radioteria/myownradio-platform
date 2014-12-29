package gemini.myownradio.tools;

import java.io.IOException;
import java.nio.Buffer;
import java.nio.ByteBuffer;

/**
 * Created by Roman on 26.12.2014.
 */
public class CircularByteBuffer {

    private long count;
    private ByteBuffer buffer;
    private int length;

    // Default read timeout is 5 seconds.
    private static final long DEFAULT_TIMEOUT = 5_000L;

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

        count += len;

        synchronized (this) {
            notifyAll();
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

    /*
      Will read buffer contents which written after specified position.
      In case if no data written after specified position method will
      be blocked until new data arrive.
    */
    public int read(long after, byte[] b, int off, int len) throws IOException {

        long threshold = System.currentTimeMillis() + timeout;

        ByteBuffer bb;

        while (threshold > System.currentTimeMillis()) {

            if (count <= after) {

                synchronized (this) {
                    try { wait(timeout); }
                    catch (InterruptedException cannotHappen) { /* NOP */ }
                }

            } else {

                int newBytes;

                synchronized (this) {
                    newBytes = (int) (count - after);
                    bb = buffer.duplicate();
                }

                if (newBytes > len) {
                    bb.position(length - len);
                    bb.get(b, off, len);
                    return len;
                } else {
                    bb.position(length - newBytes);
                    bb.get(b, off, newBytes);
                    return newBytes;
                }

            }

        }

        throw new IOException("Data awaiting timed out");

    }

}
