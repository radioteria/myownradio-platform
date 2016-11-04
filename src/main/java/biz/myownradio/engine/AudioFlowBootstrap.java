package biz.myownradio.engine;

import biz.myownradio.LHttp.LHttpException;
import biz.myownradio.LHttp.LHttpProtocol;
import biz.myownradio.engine.buffer.ConcurrentBuffer;
import biz.myownradio.engine.buffer.ConcurrentBufferKey;
import biz.myownradio.engine.buffer.ConcurrentBufferRepository;
import biz.myownradio.engine.entity.Client;
import biz.myownradio.engine.entity.Stream;
import biz.myownradio.exception.RadioException;
import biz.myownradio.ff.FFEncoderBuilder;
import biz.myownradio.tools.ClientCounter;
import biz.myownradio.tools.MORLogger;

import java.io.IOException;
import java.sql.SQLException;

/**
 * Created by Roman on 01.10.14.
 */
public class AudioFlowBootstrap {

    final private static int LISTENING_BUFFER_SIZE = 5;

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

        int bufferSize = (encoder.getAudioFormat().getBitrate() >> 3) * LISTENING_BUFFER_SIZE;

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

        } catch (Exception e) {
            logger.exception(e);
            throw e;
        } finally {
            ClientCounter.unregisterClient(streamObject.getOwner());
        }
    }

}
