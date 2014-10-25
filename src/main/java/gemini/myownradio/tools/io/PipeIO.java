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

    private Thread t;

    private volatile boolean throwed = false;
    private IOException cause;

    public PipeIO(InputStream is, OutputStream os) {
        this.is = is;
        this.os = os;
        t = new Thread(new PipeAsync());
        t.start();
    }

    public Thread thread() {
        return t;
    }

    public boolean isThrowed() {
        return throwed;
    }

    public IOException getException() {
        return cause;
    }

    class PipeAsync implements Runnable {
        public void run() {
            Thread.currentThread().setName("PipeIO");
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
            } catch (IOException e) {
                cause = new IOException("Shutdown");
                throwed = true;
            }
        }
    }
}
