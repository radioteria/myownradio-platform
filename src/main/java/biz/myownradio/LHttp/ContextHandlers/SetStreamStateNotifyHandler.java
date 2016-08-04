package biz.myownradio.LHttp.ContextHandlers;

import biz.myownradio.LHttp.LHttpException;
import biz.myownradio.LHttp.LHttpHandler;
import biz.myownradio.LHttp.LHttpProtocol;
import biz.myownradio.engine.buffer.ConcurrentBuffer;
import biz.myownradio.engine.buffer.ConcurrentBufferRepository;

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
