package gemini.myownradio.engine.buffer;

import java.util.HashMap;
import java.util.Map;
import java.util.stream.Stream;

/**
 * Created by Roman on 02.10.14.
 *
 * Repository of currently streaming audio flows
 */
public class ConcurrentBufferRepository {

    private static Object locker = new Object();
    private static Map<ConcurrentBufferKey, ConcurrentBuffer> repository =
            new HashMap<>();

    public static boolean BCExists(ConcurrentBufferKey streamKey) {
        synchronized (locker) {
            return repository.containsKey(streamKey);
        }
    }

    public static ConcurrentBuffer getBC(ConcurrentBufferKey streamKey) {
        synchronized (locker) {
            return repository.get(streamKey);
        }
    }

    public static ConcurrentBuffer createBC(ConcurrentBufferKey streamKey, int size) {
        synchronized (locker) {
            ConcurrentBuffer temp;
            repository.put(streamKey, temp = new ConcurrentBuffer(streamKey, size));
            return temp;
        }
    }

    public static void deleteBC(ConcurrentBufferKey streamKey) {
        synchronized (locker) {
            Map<ConcurrentBufferKey, ConcurrentBuffer> temp =
                    new HashMap<>(repository);
            temp.remove(streamKey);
            repository = temp;
        }
    }

    public static Stream<ConcurrentBufferKey> getKeys() {
        synchronized (locker) {
            return repository.keySet().stream();
        }
    }

}
