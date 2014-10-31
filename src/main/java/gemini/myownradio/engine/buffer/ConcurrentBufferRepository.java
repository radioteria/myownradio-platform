package gemini.myownradio.engine.buffer;

import java.util.Set;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ConcurrentMap;

/**
 * Created by Roman on 02.10.14.
 */
public class ConcurrentBufferRepository {

    private static ConcurrentMap<ConcurrentBufferKey, ConcurrentBuffer> repository =
            new ConcurrentHashMap<>();

    public static boolean BCExists(ConcurrentBufferKey streamKey) {
        return repository.containsKey(streamKey);
    }

    public static ConcurrentBuffer getBC(ConcurrentBufferKey streamKey) {
        return repository.get(streamKey);
    }

    public static ConcurrentBuffer createBC(ConcurrentBufferKey streamKey, int size) {
        ConcurrentBuffer temp;
        repository.put(streamKey, temp = new ConcurrentBuffer(streamKey, size));
        return temp;
    }

    public static void deleteBC(ConcurrentBufferKey streamKey) {
        ConcurrentMap<ConcurrentBufferKey, ConcurrentBuffer> temp =
                new ConcurrentHashMap<>(repository);
        temp.remove(streamKey);
        repository = temp;
    }

    public static Set<ConcurrentBufferKey> getKeys() {
        return repository.keySet();
    }

}
