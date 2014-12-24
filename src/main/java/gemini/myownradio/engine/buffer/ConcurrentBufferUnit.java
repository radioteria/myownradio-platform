package gemini.myownradio.engine.buffer;

import java.io.IOException;
import java.nio.ByteBuffer;

/**
 * Created by Roman on 02.10.14.
 *
 * Audio Buffer main unit
 */
public class ConcurrentBufferUnit {

    //todo: please optimize

    // Buffer data array
    private byte[] buffer;

    // Virtual data cursor position
    private long cursor;

    // Buffer data accessed time
    private long touched;

    // Use byte buffer
    protected volatile byte[] byteBuffer; // todo: I believe it could work without this thing

    // Buffer size variable
    protected int buffSize;

    // Buffer Unit initialization
    public ConcurrentBufferUnit(int size) {

        this.buffer = new byte[size];

        for (int i = 0; i < buffer.length; i++) {
            buffer[i] = 0x00;
        }

        this.buffSize = size;

        this.touched = System.currentTimeMillis();

        this.saveData();

    }

    private void saveData() {
        byteBuffer = ByteBuffer.allocate(8 + this.buffSize).putLong(cursor).put(buffer).array();
    }

    public void write(byte[] data) throws IOException {

        if (this.getTouched() > 30_000L) {
            System.out.println("DEBUG: No consumers");
            throw new IOException("No consumers");
        }

        if (data.length > this.buffSize) {
            throw new RuntimeException("Data size greater than buffer size");
        }

        if (data.length == 0) {
            return;
        }

        synchronized (this) {

            System.arraycopy(buffer, data.length, buffer, 0, buffSize - data.length);
            System.arraycopy(data, 0, buffer, buffSize - data.length, data.length);

            cursor += data.length;

            this.saveData();
            this.notifyAll();

        }

    }

    public long getTouched() {
        return System.currentTimeMillis() - touched;
    }

    public int getBufferSize() {
        return this.buffSize;
    }

    public byte[] getByteBuffer() {
        this.touched = System.currentTimeMillis();
        return this.byteBuffer;
    }

}
