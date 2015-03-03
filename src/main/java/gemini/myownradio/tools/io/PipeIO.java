package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 09.10.14.
 */
public class PipeIO {

    private InputStream is;
    private OutputStream os;

    private Thread thread;

    private volatile boolean throwed;
    private IOException cause;

    private boolean autoClose;

    public PipeIO(InputStream is, OutputStream os) {
        this(is, os, false);
    }

    public PipeIO(InputStream is, OutputStream os, boolean autoClose) {

        this.is = is;
        this.os = os;
        this.autoClose = autoClose;
        this.throwed = false;

        this.thread = new Thread(() -> {
            try (InputStream tmp = is) {
                IOTools.copy(tmp, os, true);
            } catch (IOException e) {
                cause = new IOException("Shutdown");
                throwed = true;
            } finally {
                if (this.autoClose) {
                    try {
                        this.os.close();
                    } catch (IOException e) {
                        /* NOP */
                    }
                }
            }
        });

        this.thread.setName("PipeIO");
        this.thread.start();

    }

    public Thread thread() {
        return thread;
    }

    public boolean isThrowed() {
        return throwed;
    }

    public IOException getException() {
        return cause;
    }

}
