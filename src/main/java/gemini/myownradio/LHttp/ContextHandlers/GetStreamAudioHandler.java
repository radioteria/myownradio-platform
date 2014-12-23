package gemini.myownradio.LHttp.ContextHandlers;

import gemini.myownradio.engine.AudioFlowBootstrap;
import gemini.myownradio.exception.RadioException;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.flow.AudioFormatsRegister;
import gemini.myownradio.LHttp.LHttpException;
import gemini.myownradio.LHttp.LHttpHandler;
import gemini.myownradio.LHttp.LHttpProtocol;

import java.io.IOException;
import java.sql.SQLException;

/**
 * Created by Roman on 16.10.14.
 */
public class GetStreamAudioHandler implements LHttpHandler {

    public void handler(LHttpProtocol exchange) throws IOException {

        String stream = exchange.getParameter("s").orElseThrow(LHttpException::badRequest);
        boolean metadata = exchange.headerEquals("icy-metadata", "1");

        String format = exchange.getParameter("f", "mp3_128k");

        FFEncoderBuilder decoder = AudioFormatsRegister.analyzeFormat(format);

        try {
            AudioFlowBootstrap radio = new AudioFlowBootstrap(exchange, stream, decoder, metadata);
            radio.startStreamer();
        } catch (SQLException | RadioException e) {
            e.printStackTrace();
        }

    }

}
