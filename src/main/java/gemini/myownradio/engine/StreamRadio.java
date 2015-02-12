package gemini.myownradio.engine;

import gemini.myownradio.engine.buffer.ConcurrentBuffer;
import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.engine.entity.Stream;
import gemini.myownradio.engine.entity.Track;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.flow.AbstractPlayer;
import gemini.myownradio.flow.TrackPlayer;
import gemini.myownradio.tools.MORLogger;
import gemini.myownradio.tools.MORSettings;
import gemini.myownradio.tools.ThreadTools;
import gemini.myownradio.tools.io.NullOutputStream;
import gemini.myownradio.tools.io.ThrottledOutputStream;
import gemini.myownradio.tools.io.ThroughOutputStream;

import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14.
 */
public class StreamRadio implements Runnable {

    private ConcurrentBuffer broadcast;
    private Stream stream;
    private FFEncoderBuilder decoder;

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.PLAYER);

    public StreamRadio(ConcurrentBuffer concurrentBuffer, FFEncoderBuilder decoder, Stream stream) {
        this.broadcast = concurrentBuffer;
        this.stream = stream;
        this.decoder = decoder;

        logger.sprintf("New streamer thread initialized");
    }

    public void run() {

        try (
                OutputStream flow = broadcast.getOutputStream();
                OutputStream raw = new ThroughOutputStream(flow, new FileOutputStream("/tmp/flow_" + broadcast.getStreamKey().toString() + ".log", true), decoder.generate());
                OutputStream thr = new ThrottledOutputStream(raw, 176400, 5)
        ) {
            logger.println("---- FLOW START ----");
            this.MakeFlow(thr);
            logger.println("---- FLOW STOP  ----");
        } catch (IOException e) {
            logger.exception(e);
        } finally {
            logger.sprintf("Destroying streamer thread");
            ConcurrentBufferRepository.deleteBC(this.broadcast.getStreamKey());
            logger.sprintf("Calling garbage collector");
            System.gc();
        }

    }

    public void MakeFlow(OutputStream output) {

        Track trackItem;
        AbstractPlayer trackPlayer;

        int preloadTime = MORSettings.getFirstInteger("server", "stream_preload").orElse(5) * 1000;

        logger.sprintf("Streamer preload time=%d", preloadTime);

        Boolean firstPlayingTrack = true;
        int trackSkipTimes = 0;

        while (true) {

            try {

                trackItem = stream.reload().getNowPlaying(firstPlayingTrack ? preloadTime : 0);


                if (trackItem.getTimeRemainder() < 1000) {
                    ThreadTools.Sleep(trackItem.getTimeRemainder());
                    continue;
                }

                logger.sprintf("Now playing: %s (start: %d ms, remainder: %d ms)",
                        trackItem.getTitle(), trackItem.getTrackOffset(), trackItem.getTimeRemainder());

                try {
                    // Normally we initialize track player
                    trackPlayer = new TrackPlayer(broadcast, output, trackItem.getPath().getAbsolutePath(),
                            (trackItem.getOrderIndex() % 4 == 0) && (trackItem.getTrackOffset() < 2000L));
                    broadcast.setTitle(trackItem.getTitle());
                    trackSkipTimes = 0;
                } catch (FileNotFoundException e) {
                    logger.sprintf("File not found: %s", e.getMessage());
                    if (trackSkipTimes >= 5) {
                        logger.sprintf("Too many skip attempts. Stopping streamer");
                        return;
                    }
                    stream.skipMilliseconds(trackItem.getTimeRemainder());
                    trackSkipTimes ++;
                    logger.sprintf("Skip attempt: %d", trackSkipTimes);
                    continue;
                }

                logger.println("---- PLAYER START ----");
                trackPlayer.play(trackItem.getTrackOffset());
                logger.println("---- PLAYER STOP  ----");

            } catch (Exception e) {
                // Terminate streamer on any exception
                logger.exception(e);
                return;
            }

            firstPlayingTrack = false;

        }


    }

}
