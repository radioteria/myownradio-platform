package gemini.myownradio.engine.buffer;

import gemini.myownradio.exception.NoConsumersException;
import gemini.myownradio.tools.ByteTools;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.util.Arrays;

/**
 * Created by Roman on 02.10.14.
 * <p>
 * Audio Buffer main unit
 */
public class ConcurrentBufferUnit {

    // ByteBuffer helper
    private ByteBuffer longBuffer = ByteBuffer.allocate(Long.BYTES);
    // Use byte buffer
    private volatile byte[] byteBuffer;
    // Buffer size variable
    private int buffSize;
    // Buffer data array
    //private byte[] buffer;
    // Virtual data cursor position
    //private long cursor;
    // Buffer data accessed time
    private long touched;

    // Buffer Unit initialization
    public ConcurrentBufferUnit(int size) {

        this.byteBuffer = new byte[Long.BYTES + size];

        Arrays.fill(this.byteBuffer, (byte) 0x00);

        this.buffSize = size;

        this.touched = System.currentTimeMillis();

        //this.saveData();

    }

    private void saveData() {
        //byteBuffer = ByteBuffer.allocate(8 + this.buffSize).putLong(cursor).put(buffer).array();
    }

    public void write(byte[] data) throws IOException {

        if (this.getTouched() > 30_000L) {
            throw new NoConsumersException("No consumers");
        }

        if (data.length > this.buffSize) {
            throw new RuntimeException("Data size greater than buffer size");
        }

        if (data.length == 0) {
            return;
        }


        byte[] temp = this.byteBuffer;

        long cursor = ByteBuffer.wrap(temp, 0, Long.BYTES).getLong();
        cursor += data.length;

//            System.arraycopy(buffer, data.length, buffer, 0, buffSize - data.length);
//            System.arraycopy(data, 0, buffer, buffSize - data.length, data.length);


        System.arraycopy(temp, Long.BYTES + data.length, temp, Long.BYTES, buffSize - data.length);
        System.arraycopy(data, 0, temp, Long.BYTES + buffSize - data.length, data.length);
        System.arraycopy(ByteTools.longToBytes(cursor), 0, temp, 0, Long.BYTES);

        //longBuffer.clear();

        synchronized (this) {

            this.byteBuffer = temp;

            //this.saveData();
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
