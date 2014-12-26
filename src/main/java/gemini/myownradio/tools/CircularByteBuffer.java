package gemini.myownradio.tools;

import java.io.IOException;
import java.nio.ByteBuffer;

/**
 * Created by Roman on 26.12.2014.
 */
public class CircularByteBuffer {

    private volatile long count;
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

        count += len;

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

    public int read(long after, byte[] b, int off, int len) throws IOException {

        // Will read buffer contents which written after specified position.
        // In case if no data written after specified position method will
        // be blocked until new data arrive.


        long threshold = System.currentTimeMillis() + timeout;

        while (threshold > System.currentTimeMillis()) {

            if (count <= after) {

                synchronized (this) {
                    try { wait(timeout); }
                    catch (InterruptedException cannotHappen) { /* NOP */ }
                }

            } else {

                System.out.printf("Reading from %d position when current position is %d\n", after, count);
                System.out.println(1);
                int newBytes = (int) (count - after);
                System.out.println(2);

                ByteBuffer temp = buffer.duplicate();
                System.out.println(3);

                if (newBytes > len) {
                    System.out.println(4);
                    temp.position(length - len);
                    temp.get(b, off, len);
                    return len;
                } else {
                    System.out.println(5);
                    temp.position(length - newBytes);
                    temp.get(b, off, newBytes);
                    return newBytes;
                }

            }

        }

        throw new IOException("Data wait timeout");

    }

}
