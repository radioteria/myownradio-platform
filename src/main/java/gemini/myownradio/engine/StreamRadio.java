package gemini.myownradio.engine;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.engine.entity.Stream;
import gemini.myownradio.engine.entity.Track;
import gemini.myownradio.exception.RadioException;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.flow.AbstractPlayer;
import gemini.myownradio.flow.NoisePlayer;
import gemini.myownradio.flow.TrackPlayer;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.tools.MORSettings;
import gemini.myownradio.tools.ThreadTools;
import gemini.myownradio.tools.io.ThrottledOutputStream;
import gemini.myownradio.tools.io.ThroughOutputStream;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.OutputStream;
import java.sql.SQLException;

/**
 * Created by Roman on 02.10.14.
 */
public class StreamRadio implements Runnable {

    private ConcurrentBuffer broadcast;
    private Stream stream;
    private FFEncoderBuilder decoder;

    public StreamRadio(ConcurrentBuffer concurrentBuffer, FFEncoderBuilder decoder, Stream stream) {
        this.broadcast = concurrentBuffer;
        this.stream = stream;
        this.decoder = decoder;
    }

    public void run() {

        try (
                OutputStream flow = broadcast.getOutputStream();
                OutputStream raw = new ThroughOutputStream(flow, System.err, decoder.generate());
                OutputStream thr = new ThrottledOutputStream(raw, 176400, 5);
        ) {
            this.MakeFlow(thr);
        } catch (IOException e) {
            System.out.println("Streamer exception: " + e.getMessage());
        } finally {
            ConcurrentBufferRepository.deleteBC(this.broadcast.getStreamKey());
            System.gc();
        }

    }

    public void MakeFlow(OutputStream output) {

        Track track;
        AbstractPlayer player;

        int preloadTime = MORSettings.getFirstInteger("server", "stream_preload", 5) * 1000;

        int firstPlayingTrack = 0;
        Long beforeEnd = null;

        try {

            while (true) {

                try {
                    track = stream.reload().getNowPlaying(firstPlayingTrack == 0 ? preloadTime : 0);

                    beforeEnd = track.getDuration() - track.getTrackOffset();

                    if ((beforeEnd >> 11) == 0L) {
                        ThreadTools.Sleep(beforeEnd);
                        continue;
                    }

                    player = new TrackPlayer(broadcast, output, track.getPath().getAbsolutePath(),
                            (track.getOrderIndex() % 4 == 0) && (track.getTrackOffset() < 2000L));

                    broadcast.setTitle(track.getTitle());

                    BaseLogger.writeLog(String.format("Stream %d listening to %s",
                            this.stream.getId(), track.getTitle()));

                    player.play(track.getTrackOffset());

                } catch (FileNotFoundException e) {

                } catch (RadioException e) {
                    player = new NoisePlayer(broadcast, output);
                    broadcast.setTitle("Track not found");
                    player.play();
                } catch (SQLException e) {
                    // Terminate player if database is unreachable
                    return;
                }

                firstPlayingTrack++;

            }

        } catch (IOException e) {
            /* NOP */
        }
    }

}
