package gemini.myownradio.tools.io.SharedFile;

import java.io.Closeable;
import java.io.File;
import java.io.IOException;
import java.io.RandomAccessFile;

/**
 * Created by roman on 13.03.15.
 */
public class SharedFile implements Closeable {

    public volatile int consumers = 0;
    private final long length;
    final private RandomAccessFile randomAccessFile;
    final private SharedFileReader reader;
    final private File file;

    public SharedFile(File file, SharedFileReader reader) throws IOException {
        System.out.println("Created new file object: " + file.getName());
        this.reader = reader;
        this.file = file;
        this.randomAccessFile = new RandomAccessFile(file, "r");
        this.length = this.randomAccessFile.length();
    }

    public synchronized int readOffset(long offset, byte[] buffer) throws IOException {
        return readOffset(offset, buffer, 0, buffer.length);
    }

    public synchronized int readOffset(long offset, byte[] b, int off, int len) throws IOException {
        if (offset == length) {
            return 0;
        } else if (offset > length) {
            throw new IOException("Seek position is greater than file length");
        }
        randomAccessFile.seek(offset);
        return randomAccessFile.read(b, off, len);
    }

    public synchronized void increaseConsumers() {
        System.out.println("New consumer: " + file.getName());
        consumers++;
    }

    public synchronized void decreaseConsumers() {
        if (consumers == 0) {
            throw new IllegalArgumentException("Number of consumers could not be negative");
        }
        System.out.println("Consumer gone: " + file.getName());
        consumers--;
    }

    public synchronized void checkAndClose() throws IOException {
        if (consumers == 0) {
            System.out.println("Closing file object: " + file.getName());
            this.randomAccessFile.close();
            this.reader.close(this.file);
        }
    }

    public synchronized int getConsumersCount() {
        return consumers;
    }

    public synchronized void close() throws IOException {
        this.randomAccessFile.close();
    }

    public long length() throws IOException {
        return length;
    }

}