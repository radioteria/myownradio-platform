package gemini.myownradio.engine.buffer;

import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14.
 */
public class ConcurrentBuffer {

    private ConcurrentBufferKey key;
    private int size;
    private ConcurrentBufferCreator buffer;
    private volatile String title;

    private volatile boolean notify = false;

    public ConcurrentBuffer(ConcurrentBufferKey streamKey, int size) {
        this.key = streamKey;
        this.size = size;
        this.buffer = new ConcurrentBufferCreator(size);
        this.title = "";
    }

    public ConcurrentBufferCreator getBCBuffer() {
        return this.buffer;
    }

    public OutputStream getOutputStream() {
        return new ConcurrentBufferOutput(this.buffer);
    }

    public InputStream getInputStream() {
        return new ConcurrentBufferInput(this.buffer);
    }

    public void setTitle(String newTitle) {
        this.title = newTitle;
    }

    public String getTitle() {
        return this.title;
    }

    public ConcurrentBufferKey getStreamKey() {
        return this.key;
    }

    public boolean isNotify() {
        return notify;
    }

    public ConcurrentBuffer setNotify() {
        System.out.println(key + " notified!");
        this.notify = true;
        return this;
    }

    public ConcurrentBuffer resetNotify() {
        this.notify = false;
        return this;
    }
}
