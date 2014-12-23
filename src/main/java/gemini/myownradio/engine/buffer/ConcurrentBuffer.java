package gemini.myownradio.engine.buffer;

import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14.
 *
 * Audio flow buffer main class
 */
public class ConcurrentBuffer {

    // Buffer key which depends on audio stream id and audio quality
    private ConcurrentBufferKey key;

    // Buffer container
    private ConcurrentBufferUnit buffer;

    // Current track title
    private volatile String title;

    // This variable used to notify audio streams about need of audio flow reset
    private volatile boolean notify = false;

    public ConcurrentBuffer(ConcurrentBufferKey streamKey, int size) {
        this.key = streamKey;
        this.buffer = new ConcurrentBufferUnit(size);
        this.title = "";
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

    public boolean isNotified() {
        return notify;
    }

    public ConcurrentBuffer setNotify() {
        this.notify = true;
        return this;
    }

    public ConcurrentBuffer resetNotify() {
        this.notify = false;
        return this;
    }
}
