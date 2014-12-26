package gemini.myownradio.engine.buffer;

import gemini.myownradio.exception.NoConsumersException;
import gemini.myownradio.tools.ByteTools;

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

    // Buffer Unit initialization
    public ConcurrentBufferUnit(int size) {

        this.byteBuffer = new byte[Long.BYTES + size];

        Arrays.fill(this.byteBuffer, (byte) 0x00);

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

        byte[] temp = this.byteBuffer;

        long cursor = ByteTools.bytesToLong(temp, 0, Long.BYTES) + data.length;

        // Shift left buffer contents allocating space for new data
        System.arraycopy(temp, Long.BYTES + data.length, temp, Long.BYTES, bufferSize - data.length);
        // Save new data to allocated space in buffer
        System.arraycopy(data, 0, temp, Long.BYTES + bufferSize - data.length, data.length);
        // Update cursor position in buffer
        System.arraycopy(ByteTools.longToBytes(cursor), 0, temp, 0, Long.BYTES);

        synchronized (this) {

            this.byteBuffer = temp;
            this.notifyAll();

        }

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

}
