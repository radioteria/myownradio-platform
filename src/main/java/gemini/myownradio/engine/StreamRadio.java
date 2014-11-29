package gemini.myownradio.engine;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.engine.entity.Stream;
import gemini.myownradio.engine.entity.Track;
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
                OutputStream thr = new ThrottledOutputStream(raw, 176400, 5)
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

        Track trackItem;
        AbstractPlayer trackPlayer;

        int preloadTime = MORSettings.getFirstInteger("server", "stream_preload", 5) * 1000;

        Boolean firstPlayingTrack = true;
        int trackSkipTimes = 0;

        while (true) {

            try {

                trackItem = stream.reload().getNowPlaying(firstPlayingTrack ? preloadTime : 0);

                if ((trackItem.getTimeRemainder() >> 11) == 0L) {
                    ThreadTools.Sleep(trackItem.getTimeRemainder());
                    continue;
                }

                try {
                    // Normally we initialize track player
                    trackPlayer = new TrackPlayer(broadcast, output, trackItem.getPath().getAbsolutePath(),
                            (trackItem.getOrderIndex() % 4 == 0) && (trackItem.getTrackOffset() < 2000L));
                    broadcast.setTitle(trackItem.getTitle());
                    trackSkipTimes = 0;
                } catch (FileNotFoundException e) {
                    if (trackSkipTimes >= 5) {
                        return;
                    }
                    // If track file not exists we initialize noise player
                    //trackPlayer = new NoisePlayer(broadcast, output, 1000L);
                    //broadcast.setTitle(trackItem.getTitle() + " (file not found)");
                    stream.skipMilliseconds(trackItem.getTimeRemainder());
                    trackSkipTimes ++;
                    continue;
                }

                BaseLogger.writeLog(String.format("Stream %d listening to %s",
                        this.stream.getId(), trackItem.getTitle()));

                trackPlayer.play(trackItem.getTrackOffset());

            } catch (Exception e) {
                // Terminate streamer on any exception
                return;
            }

            firstPlayingTrack = false;

        }


    }

}
