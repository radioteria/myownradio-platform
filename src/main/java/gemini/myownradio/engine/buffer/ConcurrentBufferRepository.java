package gemini.myownradio.engine.buffer;

import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.stream.Stream;

/**
 * Created by Roman on 02.10.14.
 * <p>
 * Repository of currently streaming audio flows
 */
public class ConcurrentBufferRepository {

    private static Map<Integer, Map<ConcurrentBufferKey, ConcurrentBuffer>> repo =
            new ConcurrentHashMap<>();

    public static boolean isBufferExists(ConcurrentBufferKey streamKey) {
        int streamId = streamKey.getStream();
        return repo.containsKey(streamId) && repo.get(streamId).containsKey(streamKey);
    }

    public static ConcurrentBuffer getBuffer(ConcurrentBufferKey streamKey) {
        int streamId = streamKey.getStream();
        return repo.getOrDefault(streamId, Collections.emptyMap()).get(streamKey);
    }

    public static ConcurrentBuffer createBuffer(ConcurrentBufferKey streamKey, int size) {
        ConcurrentBuffer buffer;
        int streamId = streamKey.getStream();
        repo.computeIfAbsent(streamId, v -> new ConcurrentHashMap<>())
                .put(streamKey, buffer = new ConcurrentBuffer(streamKey, size));
        return buffer;
    }

    public static void deleteBuffer(ConcurrentBufferKey streamKey) {
        int streamId = streamKey.getStream();
        repo.getOrDefault(streamId, Collections.emptyMap())
                .remove(streamKey);
    }

    public static Stream<ConcurrentBuffer> getBuffersByStream(int streamId) {
        return repo.getOrDefault(streamId, Collections.emptyMap())
                .values()
                .stream();
    }

}
