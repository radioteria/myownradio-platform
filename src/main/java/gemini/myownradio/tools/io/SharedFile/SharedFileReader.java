package gemini.myownradio.tools.io.SharedFile;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Created by roman on 13.03.15.
 */
public class SharedFileReader {

    private SharedFile sharedFile;
    final private static Map<File, SharedFile> handles = new ConcurrentHashMap<>();
    final private static Object lock = new Object();

    public SharedFileReader(File file) throws IOException {

        synchronized (lock) {
            if (handles.containsKey(file)) {
                sharedFile = handles.get(file);
            } else {
                sharedFile = new SharedFile(file, this);
                handles.put(file, sharedFile);
            }
        }

    }

    public InputStream getInputStream() throws IOException {
        return new SharedFileStream(sharedFile);
    }

    public synchronized void close(File file) {
        handles.remove(file);
    }

}
