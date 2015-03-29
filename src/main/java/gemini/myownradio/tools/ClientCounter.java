package gemini.myownradio.tools;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicInteger;

/**
 * Created by roman on 28.03.15.
 */
public class ClientCounter {
    private static Map<Integer, AtomicInteger> clients = new ConcurrentHashMap<>();

    public static int getClientsByStreamId(int streamId) {
        if (!clients.containsKey(streamId)) { return 0; }
        return clients.get(streamId).get();
    }

    public static void registerNewClient(int streamId) {
        if (!clients.containsKey(streamId)) {
            clients.put(streamId, new AtomicInteger(1));
        } else {
            clients.get(streamId).incrementAndGet();
        }
    }

    public static void unregisterClient(int streamId) {
        if (clients.containsKey(streamId)) {
            if (clients.get(streamId).get() == 1) {
                clients.remove(streamId);
            } else {
                clients.get(streamId).decrementAndGet();
            }
        }
    }

    public static Map<Integer, AtomicInteger> getClients() {
        return clients;
    }

}
