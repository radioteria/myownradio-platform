package gemini.myownradio.LHttp.ContextHandlers;

import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.LHttp.LHttpException;
import gemini.myownradio.LHttp.LHttpHandler;
import gemini.myownradio.LHttp.LHttpProtocol;
import gemini.myownradio.LHttp.LHttpStatus;

import java.io.IOException;

/**
 * Created by Roman on 16.10.14.
 */
public class SetStreamStateNotifyHandler implements LHttpHandler {

    public void handler(LHttpProtocol exchange) throws IOException {

        if (!exchange.getClientIP().equals("127.0.0.1")) {
            exchange.setStatus(LHttpStatus.STATUS_403);
            exchange.setContentType("text/html");
            exchange.getPrinter().println("<h1>HTTP/1.1 403 Forbidden</h1>");
            exchange.flush();
            return;
        }

        final int stream_id = Integer.parseInt(exchange.getParameter("s").orElseThrow(() -> LHttpException.newBadRequest()));

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
