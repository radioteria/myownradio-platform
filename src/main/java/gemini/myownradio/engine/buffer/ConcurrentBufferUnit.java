package gemini.myownradio.engine.buffer;

import com.sun.corba.se.impl.encoding.CodeSetConversion;
import gemini.myownradio.exception.NoConsumersException;

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

    // ByteBuffer helper
    private static ByteBuffer longBuffer = ByteBuffer.allocate(Long.BYTES);

    // Buffer size variable
    protected int buffSize;

    // Buffer Unit initialization
    public ConcurrentBufferUnit(int size) {

        this.byteBuffer = new byte[Long.BYTES + size];

        for (int i = 0; i < byteBuffer.length; i++) {
            this.byteBuffer[i] = 0x00;
        }

        this.buffSize = size;

        this.touched = System.currentTimeMillis();

        //this.saveData();

    }

    private void saveData() {
        byteBuffer = ByteBuffer.allocate(8 + this.buffSize).putLong(cursor).put(buffer).array();
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

        System.out.println("Cursor: " + cursor);

//            System.arraycopy(buffer, data.length, buffer, 0, buffSize - data.length);
//            System.arraycopy(data, 0, buffer, buffSize - data.length, data.length);

            System.arraycopy(temp, Long.BYTES + data.length, temp, Long.BYTES, buffSize - data.length);
            System.arraycopy(data, 0, temp, Long.BYTES + buffSize - data.length, data.length);
            System.arraycopy(longBuffer.putLong(cursor).array(), 0, temp, 0, Long.BYTES);

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
