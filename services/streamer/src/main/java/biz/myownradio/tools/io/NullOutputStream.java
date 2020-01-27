package biz.myownradio.tools.io;

import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 08.10.14.
 */
public class NullOutputStream extends OutputStream {
    public NullOutputStream() {

    }

    @Override
    public void write(int b) throws IOException {
        /*NOP*/
    }

    @Override
    public void write(byte[] b) throws IOException {
        /*NOP*/
    }

    @Override
    public void write(byte[] b, int off, int len) throws IOException {
        /*NOP*/
    }

    @Override
    public void flush() throws IOException {
        /*NOP*/
    }

    @Override
    public void close() throws IOException {
        /*NOP*/
    }
}
