package biz.myownradio.engine.buffer;

import biz.myownradio.tools.CircularByteBuffer;
import biz.myownradio.tools.MORLogger;

import java.io.IOException;
import java.io.InputStream;

/**
 * Created by Roman on 02.10.14.
 * <p>
 * InputStream layer for radio clients
 */
public class ConcurrentBufferInput extends InputStream {

    final private ConcurrentBufferUnit bufferUnit;

    private CircularByteBuffer bb;
    private long count;

    final private static MORLogger logger = new MORLogger(MORLogger.MessageKind.BUFFER);

    public ConcurrentBufferInput(ConcurrentBufferUnit bufferUnit) {
        this.bufferUnit = bufferUnit;
        this.bb = bufferUnit.getCircularByteBuffer();
        this.count = this.bb.getBeginning();
        logger.sprintf("Started Circular Buffer from position %d", this.count);
    }

    @Override
    public int read() throws IOException {
        byte[] tmp = new byte[1];
        int len = this.read(tmp, 0, tmp.length);
        if (len == 1) {
            return (int) tmp[0];
        } else {
            return -1;
        }
    }

    @Override
    public int read(byte[] b) throws IOException {
        return this.read(b, 0, b.length);
    }

    @Override
    public int read(byte[] b, int off, int len) throws IOException {

        int length = bb.read(count, b, off, len);

        bufferUnit.touch();

        count += length;

        return length;

    }

}