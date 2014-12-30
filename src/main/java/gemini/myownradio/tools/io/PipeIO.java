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

    private volatile boolean throwed = false;
    private IOException cause;

    private boolean autoClose;

    public PipeIO(InputStream is, OutputStream os) {
        this(is, os, false);
    }

    public PipeIO(InputStream is, OutputStream os, boolean autoClose) {

        this.is = is;
        this.os = os;
        this.autoClose = autoClose;

        this.thread = new Thread(() -> {
            try (InputStream tmp = is) {
                byte[] buffer = new byte[4096];
                int len;
                while ((len = tmp.read(buffer)) != -1) {
                    if (Thread.interrupted()) {
                        return;
                    }
                    os.write(buffer, 0, len);
                    os.flush();
                }
                if(this.autoClose) {
                    os.close();
                }
            } catch (IOException e) {
                cause = new IOException("Shutdown");
                throwed = true;
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
