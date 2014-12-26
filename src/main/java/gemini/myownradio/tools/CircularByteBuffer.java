package gemini.myownradio.tools;

import java.nio.ByteBuffer;

/**
 * Created by Roman on 26.12.2014.
 */
public class CircularByteBuffer {

    private long count;
    private ByteBuffer buffer;
    private int length;

    public CircularByteBuffer(int size) {
        this.count = 0L;
        this.buffer = ByteBuffer.allocateDirect(size);
        this.length = size;
    }

    public void putBytes(byte[] b, int pos, int len) {
        buffer.position(len);
        buffer.compact();
        buffer.put(b, pos, len);
        count =+ len;
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

}
