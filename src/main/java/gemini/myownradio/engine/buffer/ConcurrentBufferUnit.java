package gemini.myownradio.engine.buffer;

import gemini.myownradio.exception.NoConsumersException;
import gemini.myownradio.tools.CircularByteBuffer;

import java.io.IOException;
import java.util.Arrays;

/**
 * Created by Roman on 02.10.14.
 * <p>
 * Audio Buffer main unit
 */
public class ConcurrentBufferUnit {

    // Use byte buffer
    private byte[] byteBuffer;
    // Buffer size variable
    private int bufferSize;

    private long touched;

    private CircularByteBuffer circularByteBuffer;

    // Buffer Unit initialization
    public ConcurrentBufferUnit(int size) {

        //this.byteBuffer = new byte[Long.BYTES + size];
        this.circularByteBuffer = new CircularByteBuffer(size);

        //Arrays.fill(this.byteBuffer, (byte) 0x00);

        this.bufferSize = size;

        this.touched = System.currentTimeMillis();

    }

    public void write(byte[] data) throws IOException {

        if (this.getTouched() > 30_000L) {
            throw new NoConsumersException("No consumers");
        }

        if (data.length > this.bufferSize) {
            throw new RuntimeException("Data size greater than buffer size");
        }

        if (data.length == 0) {
            return;
        }

        circularByteBuffer.putBytes(data);

    }

    public long getTouched() {
        return System.currentTimeMillis() - touched;
    }

    public int getBufferSize() {
        return this.bufferSize;
    }

    public byte[] getByteBuffer() {
        this.touched = System.currentTimeMillis();
        return this.byteBuffer;
    }

    public void touch() {
        this.touched = System.currentTimeMillis();
    }

    public CircularByteBuffer getCircularByteBuffer() {
        return circularByteBuffer;
    }

}
