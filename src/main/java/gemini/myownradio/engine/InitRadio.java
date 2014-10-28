package gemini.myownradio.engine;

import gemini.myownradio.exception.RadioException;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.light.LHttpProtocol;
import gemini.myownradio.engine.entity.Stream;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferKey;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;
import java.sql.SQLException;

/**
 * Created by Roman on 01.10.14.
 */
public class InitRadio {

    private LHttpProtocol exchange;

    private FFEncoderBuilder encoder;

    private boolean useIcyMetadata;
    private Stream streamObject;

    public InitRadio(LHttpProtocol exchange, String stream_id, FFEncoderBuilder encoder, boolean useIcyMetadata)
            throws SQLException, RadioException, IOException {

        this.streamObject = new Stream(stream_id);
        this.exchange = exchange;
        this.useIcyMetadata = useIcyMetadata;

        this.encoder = encoder;

    }

    public void startStreamer() throws IOException {

        ConcurrentBufferKey streamKey = new ConcurrentBufferKey(
                encoder.getAudioFormat().getFormat(),
                encoder.getAudioFormat().getBitrate(),
                this.streamObject.getId()
        );

        int bufferSize = (encoder.getAudioFormat().getBitrate() >> 3) * MORSettings.getFirstInteger("server", "streaming_buffer", 5);

        ConcurrentBuffer broadcast;

        BaseLogger.writeLog(String.format("Using buffer size=%d key=%s", bufferSize, streamKey.toString()));

        if ((broadcast = ConcurrentBufferRepository.getBC(streamKey)) == null) {
            broadcast = ConcurrentBufferRepository.createBC(streamKey, bufferSize);
            Thread streamer = new Thread(new StreamRadio(broadcast, encoder, streamObject));
            streamer.setName("Streamer key " + streamKey.toString());
            streamer.setDaemon(true);
            streamer.start();

        }

        ListenRadio listener = new ListenRadio(exchange, useIcyMetadata, broadcast, encoder, streamObject);
        listener.listen();

    }

}
