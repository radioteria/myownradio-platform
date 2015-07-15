package gemini.myownradio.LHttp.ContextHandlers;

import gemini.myownradio.LHttp.LHttpException;
import gemini.myownradio.LHttp.LHttpHandler;
import gemini.myownradio.LHttp.LHttpProtocol;
import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;

import java.io.IOException;

/**
 * Created by Roman on 16.10.14.
 */
public class SetStreamStateNotifyHandler implements LHttpHandler {

    public void handle(LHttpProtocol exchange) throws IOException {

        if (!exchange.getClientIP().equals("127.0.0.1")) {
            throw LHttpException.forbidden();
        }

        int stream_id;

        try {
            stream_id = Integer.parseInt(exchange.getParameter("s").orElseThrow(LHttpException::badRequest));
        } catch (NumberFormatException e) {
            throw LHttpException.badRequest();
        }

        long notified = ConcurrentBufferRepository
                .getBuffersByStream(stream_id)
                .map(ConcurrentBuffer::setNotify)
                .count();

        exchange.getPrinter().println("STREAMERS_NOTIFIED = " + notified);

    }
}
