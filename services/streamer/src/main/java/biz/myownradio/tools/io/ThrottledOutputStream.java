package biz.myownradio.tools.io;

import biz.myownradio.tools.ThreadTools;

import java.io.FilterOutputStream;
import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 07.10.14.
 */
public class ThrottledOutputStream extends FilterOutputStream {

    private int packetSpeed;

    private long start;
    private long bytes;

    private byte[] oneByte = new byte[1];

    private static final long SLEEP_DURATION_MS = 50L;


    public ThrottledOutputStream(OutputStream output, int speed, int preloading) {

        super(output);

        this.packetSpeed = speed;

        this.start = System.currentTimeMillis();
        this.bytes = -preloading * speed;

    }

    @Override
    public void write(int b) throws IOException {
        oneByte[0] = (byte) b;
        this.write(oneByte);
    }

    @Override
    public void write(byte[] b) throws IOException {
        this.write(b, 0, b.length);
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {

        this.bytes += len;

        long bps = bytes / Math.max((System.currentTimeMillis() - start) / 1000L, 1L);

        if (bps > packetSpeed) {
            ThreadTools.Sleep(SLEEP_DURATION_MS);
        }

        out.write(b, off, len);

    }

    @Override
    public void flush() throws IOException {
        super.flush();
    }

    @Override
    public void close() throws IOException {
        super.close();
    }
}
