package gemini.myownradio.light.ContextHandlers;

import gemini.myownradio.engine.buffer.ConcurrentBufferKey;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.light.LHttpHandler;
import gemini.myownradio.light.LHttpProtocol;
import gemini.myownradio.light.LHttpStatus;

import java.io.IOException;

/**
 * Created by Roman on 16.10.14.
 */
public class handlerNotify implements LHttpHandler {

    public void handler(LHttpProtocol exchange) throws IOException {

        if (!exchange.getClientIP().equals("127.0.0.1")) {
            exchange.setStatus(LHttpStatus.STATUS_403);
            exchange.setContentType("text/html");
            exchange.getPrinter().println("<h1>HTTP/1.1 403 Forbidden</h1>");
            exchange.flush();
            return;
        }

        final int stream_id;

        stream_id = Integer.parseInt(exchange.get("s"));

        long notified = ConcurrentBufferRepository
                        .getKeys()
                        .parallel()
                        .filter(o -> o.getStream() == stream_id)
                        .map(s -> ConcurrentBufferRepository.getBC(s))
                        .map(o -> o.setNotify())
                        .count();

        exchange.getPrinter().println("STREAMERS_NOTIFIED = " + notified);
    }
}
