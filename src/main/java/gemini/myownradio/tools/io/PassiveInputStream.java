package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;
import java.nio.ByteBuffer;

/**
 * Created by roman on 29.12.14.
 */
public class PassiveInputStream {
    private InputSupplier supplier;
    private int capacity;
    private long position;
    private Thread thread;
    private ByteBuffer byteBuffer;

    final private static double MIN_THRESHOLD = 0.25;
    final private static double MAX_THRESHOLD = 0.90;

    public PassiveInputStream(InputSupplier supplier, int capacity) {
        this.supplier = supplier;
        this.capacity = capacity;
        this.byteBuffer = ByteBuffer.allocateDirect(capacity);
    }

    private void pump() {
        thread = new Thread(() -> {

        });
        thread.start();
    }

    private void cache() throws IOException {
        try (InputStream inputStream = supplier.open()) {
            inputStream.skip(position);
            byte[] buffer = new byte[4096];
            int length;
            while ((length = inputStream.read(buffer)) != -1) {
                if (position + length < capacity) {
                    synchronized (this) {
                        byteBuffer.put(buffer, 0, length);
                        position += length;
                    }
                } else {
                    inputStream.close();
                }
            }
        } catch (IOException e) { /* NOP */ }
    }
}
