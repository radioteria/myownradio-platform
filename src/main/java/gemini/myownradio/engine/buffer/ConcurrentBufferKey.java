package gemini.myownradio.engine.buffer;

/**
 * Created by Roman on 05.10.14.
 */
public class ConcurrentBufferKey {
    private String format;
    private int stream;
    private int bitrate;

    public ConcurrentBufferKey(String format, int bitrate, int stream) {

        this.format = format;
        this.stream = stream;
        this.bitrate = bitrate;

    }

    public int getBitrate() {
        return bitrate;
    }

    @Override
    public String toString() {
        return String.format("[stream=%d, format=%s, bitrate=%s]", stream, format, bitrate);
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (!(o instanceof ConcurrentBufferKey)) return false;

        ConcurrentBufferKey concurrentBufferKey = (ConcurrentBufferKey) o;

        if (stream != concurrentBufferKey.stream) return false;
        if (bitrate != concurrentBufferKey.bitrate) return false;
        if (format != concurrentBufferKey.format) return false;

        return true;
    }

    @Override
    public int hashCode() {
        int result = format.hashCode();
        result = 31 * result + stream;
        result = 31 * result + bitrate;
        return result;
    }

    public int getStream() {
        return stream;
    }
}
