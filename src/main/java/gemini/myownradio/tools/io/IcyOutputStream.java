package gemini.myownradio.tools.io;

import java.io.FilterOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.io.UnsupportedEncodingException;

/**
 * Created by Roman on 11.10.14.
 */
public class IcyOutputStream extends FilterOutputStream {

    private int interval;
    private byte[] buffer;
    private int count;
    private byte[] oneByte = new byte[1];

    private byte[] meta;
    private boolean update = false;

    final public static int DEFAULT_META_INTERVAL = 8192;

    public IcyOutputStream(OutputStream out) {
        this(out, DEFAULT_META_INTERVAL);
    }

    public IcyOutputStream(OutputStream out, int interval) {
        super(out);

        if (interval <= 0) {
            throw new IllegalArgumentException("Wrong icy metadata interval!");
        }

        this.interval = interval;
        this.buffer = new byte[interval];
        this.count = 0;
    }

    int cnt = 0;

    public void setTitle(String title) {
        try {
            String formatted = String.format("StreamTitle='%s';", title);
            byte[] symbols = formatted.getBytes("UTF-8");
            byte size = (byte) Math.ceil((double) symbols.length / 16D);
            this.meta = new byte[size * 16 + 1];
            for (int i = 0; i < meta.length; i++) {
                meta[i] = 0x20;
            }
            System.arraycopy(new byte[]{size}, 0, this.meta, 0, 1);
            System.arraycopy(symbols, 0, this.meta, 1, symbols.length);
            update = true;
        } catch (UnsupportedEncodingException e) { /*NOP*/ }
    }

    private void writeMetadata() throws IOException {
        if (update) {
            this.out.write(this.meta, 0, this.meta.length);
            update = false;
        } else {
            byte[] zero = new byte[1];
            zero[0] = 0x00;
            this.out.write(zero, 0, zero.length);
        }
    }

    @Override
    public void write(int b) throws IOException {
        oneByte[0] = (byte) b;
        this.write(oneByte, 0, oneByte.length);
    }

    @Override
    public void write(byte[] b) throws IOException {
        this.write(b, 0, b.length);
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {
        if (len > interval) {
            throw new IllegalArgumentException("");
        }
        if (interval - count >= len) {
            System.arraycopy(b, off, buffer, count, len);
            count += len;
        } else {
            int left = interval - count;
            System.arraycopy(b, off, buffer, count, left);
            this.out.write(buffer, 0, interval);

            writeMetadata();

            int rest = len - left;
            System.arraycopy(b, left, buffer, 0, rest);
            this.count = rest;
        }
    }

    @Override
    public void flush() throws IOException { // todo: may be harmful
        if (count > 0) {
            this.out.write(buffer, 0, count);
            count = 0;
        }
    }

    @Override
    public void close() throws IOException {
        super.close();
    }
}
