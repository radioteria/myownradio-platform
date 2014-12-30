package gemini.myownradio.tools;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.util.Arrays;

/**
 * Created by Roman on 26.12.2014.
 */
public class CircularByteBuffer {

    private long count;
    private int length;
    private byte[] raw;

    // Default read timeout is 5 seconds.
    private static final long DEFAULT_TIMEOUT = 5_000L;

    private long timeout;

    public CircularByteBuffer(int size, long timeout) {
        this.count = 0L;
        this.raw = new byte[size + Long.BYTES];
        this.length = size;
        this.timeout = timeout;

        Arrays.fill(raw, (byte) 0x00);
    }

    public CircularByteBuffer(int size) {
        this(size, DEFAULT_TIMEOUT);
    }

    public void putBytes(byte[] b, int pos, int len) {

        long cursor = ByteTools.bytesToLong(raw);

        System.arraycopy(raw, Long.BYTES + len, raw, Long.BYTES, length - len);
        System.arraycopy(b, pos, raw, raw.length - len, len);
        System.arraycopy(ByteTools.longToBytes(cursor + len), 0, raw, 0, Long.BYTES);

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

        while (threshold > System.currentTimeMillis()) {

            if (count <= after) {

                synchronized (this) {
                    try { wait(timeout); }
                    catch (InterruptedException cannotHappen) { /* NOP */ }
                }

            } else {

                long tmpCursor;
                int newBytes;
                int length;

                synchronized (this) {
                    tmpCursor = ByteTools.bytesToLong(raw);
                    newBytes = (int) (tmpCursor - after);

                    if (newBytes > len) {
                        System.arraycopy(raw, raw.length - newBytes, b, 0, len);
                        length = len;
                    } else {
                        System.arraycopy(raw, raw.length - newBytes, b, 0, newBytes);
                        length = newBytes;
                    }

                }

                return length;

            }

        }

        throw new IOException("Data awaiting timed out");

    }

}
