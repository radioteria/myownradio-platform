package gemini.myownradio.engine;

import gemini.myownradio.LHttp.LHttpException;
import gemini.myownradio.LHttp.LHttpProtocol;
import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferKey;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.engine.entity.Client;
import gemini.myownradio.engine.entity.Stream;
import gemini.myownradio.exception.RadioException;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.tools.ClientCounter;
import gemini.myownradio.tools.MORLogger;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;
import java.sql.SQLException;

/**
 * Created by Roman on 01.10.14.
 */
public class AudioFlowBootstrap {

    private LHttpProtocol exchange;

    private FFEncoderBuilder encoder;

    private boolean useIcyMetadata;
    private Stream streamObject;

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.PLAYER);

    public AudioFlowBootstrap(LHttpProtocol exchange, int stream_id, FFEncoderBuilder encoder, boolean useIcyMetadata)
            throws SQLException, RadioException, IOException {

        this.streamObject = new Stream(stream_id);

        Client client = new Client(exchange);

        if (streamObject.getAccess().equals("PRIVATE") && (client.getUserId() == null || streamObject.getOwner() != client.getUserId())) {
            System.err.println("Forbidden");
            throw LHttpException.forbidden();
        }

        this.exchange = exchange;
        this.useIcyMetadata = useIcyMetadata;

        this.encoder = encoder;

        logger.sprintf("Starting to transmit stream (id=%s, name=%s)",
                this.streamObject.getId(), this.streamObject.getName());

    }

    public void startStreamer() throws IOException, SQLException {

        if (this.streamObject.getMaxClients() >= ClientCounter.getClientsByStreamId(streamObject.getOwner())) {
            ClientCounter.registerNewClient(streamObject.getOwner());
        } else {
            throw LHttpException.forbidden();
        }


        ConcurrentBufferKey streamKey = new ConcurrentBufferKey(
                encoder.getAudioFormat().getFormat(),
                encoder.getAudioFormat().getBitrate(),
                this.streamObject.getId()
        );

        int streamingBufferLength = MORSettings.getFirstInteger("server", "streaming_buffer").orElse(5);

        int bufferSize = (encoder.getAudioFormat().getBitrate() >> 3) * streamingBufferLength;

        ConcurrentBuffer broadcast;

        logger.sprintf("Using buffer size=%d key=%s", bufferSize, streamKey.toString());

        if ((broadcast = ConcurrentBufferRepository.getBuffer(streamKey)) == null) {
            broadcast = ConcurrentBufferRepository.createBuffer(streamKey, bufferSize);
            Thread streamer = new Thread(new StreamRadio(broadcast, encoder, streamObject));
            streamer.setName(streamKey.toString());
            streamer.setDaemon(true);
            streamer.start();
        }

        ListenRadio listener = new ListenRadio(exchange, useIcyMetadata, broadcast, encoder, streamObject);

        try {

            listener.listen();

        } finally {
            ClientCounter.unregisterClient(streamObject.getOwner());
        }
    }

}
