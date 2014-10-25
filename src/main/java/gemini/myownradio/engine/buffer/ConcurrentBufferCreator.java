package gemini.myownradio.engine.buffer;

import java.io.IOException;

/**
 * Created by Roman on 02.10.14.
 */
public class ConcurrentBufferCreator {

    final int MAX_BUFFER_SIZE = 1048576;
    final int MIN_BUFFER_SIZE = 32;

    final private int WAITING_TIME = 5000;

    int     sbSize;

    private ConcurrentBufferMemory bu;

    public ConcurrentBufferCreator(int sbSize) {

        if (sbSize < MIN_BUFFER_SIZE || sbSize > MAX_BUFFER_SIZE) {
            throw new IllegalArgumentException(
                    String.format("Buffer size must be in range %d..%d",
                            MIN_BUFFER_SIZE, MAX_BUFFER_SIZE));
        }

        this.bu = new ConcurrentBufferMemory(sbSize);

        this.sbSize = sbSize;

    }

    public synchronized void write(byte[] data, int off, int len) throws IOException {

        byte[] tmp = new byte[len];

        System.arraycopy(data, off, tmp, 0, len);

        bu.write(tmp);

    }

    public ConcurrentBufferMemory getBufferUnit() {
        return this.bu;
    }

}
