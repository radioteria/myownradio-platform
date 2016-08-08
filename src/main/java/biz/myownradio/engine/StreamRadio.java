package biz.myownradio.engine;

import biz.myownradio.engine.buffer.ConcurrentBuffer;
import biz.myownradio.engine.buffer.ConcurrentBufferRepository;
import biz.myownradio.engine.entity.Stream;
import biz.myownradio.engine.entity.Track;
import biz.myownradio.exception.DecoderException;
import biz.myownradio.ff.FFEncoderBuilder;
import biz.myownradio.flow.AbstractPlayer;
import biz.myownradio.flow.TrackPlayer;
import biz.myownradio.tools.Logger;
import biz.myownradio.tools.ThreadTools;
import biz.myownradio.tools.io.ThrottledOutputStream;
import biz.myownradio.tools.io.ThroughOutputStream;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by Roman on 02.10.14
 */
class StreamRadio implements Runnable
{
    final private static Logger logger = new Logger(Logger.MessageKind.PLAYER);

    private ConcurrentBuffer broadcast;
    private Stream stream;
    private FFEncoderBuilder decoder;

    StreamRadio(ConcurrentBuffer concurrentBuffer, FFEncoderBuilder decoder, Stream stream) {
        this.broadcast = concurrentBuffer;
        this.stream = stream;
        this.decoder = decoder;

        logger.print("New streamer thread initialized");
    }

    public void run()
    {
        try (
                OutputStream flow = broadcast.getOutputStream();
                OutputStream raw = new ThroughOutputStream(flow, decoder.generate());
                OutputStream thr = new ThrottledOutputStream(raw, 176400, 5)
        ) {
            logger.print("---- FLOW START ----");
            this.makeFlow(thr);
            logger.print("---- FLOW STOP  ----");
        } catch (IOException e) {
            logger.exception(e);
        } finally {
            logger.print("Destroying streamer thread");
            ConcurrentBufferRepository.deleteBuffer(this.broadcast.getStreamKey());
            logger.print("Calling garbage collector");
            System.gc();
        }
    }

    private void makeFlow(OutputStream output)
    {
        Track trackItem;
        AbstractPlayer trackPlayer;

        int preloadTime = 5000;

        logger.printf("Streamer preload time=%d", preloadTime);

        Boolean firstPlayingTrack = true;
        int trackSkipTimes = 0;

        while (!Thread.currentThread().isInterrupted()) {

            try {

                trackItem = stream.reload().getNowPlaying(firstPlayingTrack ? preloadTime : 0);

                if (trackItem.getTimeRemainder() < 1000) {
                    ThreadTools.Sleep(trackItem.getTimeRemainder());
                    continue;
                }

                logger.printf("Now playing: %s (start: %d ms, remainder: %d ms)",
                        trackItem.getTitle(), trackItem.getTrackOffset(), trackItem.getTimeRemainder());

                try {
                    // Normally we initialize track player
                    trackPlayer = new TrackPlayer(broadcast, output, trackItem.getFileUrl(),
                            (trackItem.getOrderIndex() % stream.getJingleInterval() == 0) && (trackItem.getTrackOffset() < 2000L));

                    broadcast.setTitle(trackItem.getTitle());

                } catch (FileNotFoundException e) {
                    logger.printf("File not found: %s", e.getMessage());
                    if (trackSkipTimes >= 5) {
                        logger.print("Too many skip attempts. Stopping streamer");
                        return;
                    }
                    stream.skipMilliseconds(trackItem.getTimeRemainder());
                    trackSkipTimes ++;
                    logger.printf("Skip attempt: %d", trackSkipTimes);
                    continue;
                }

                logger.print("---- PLAYER START ----");
                try {
                    trackPlayer.play(trackItem.getTrackOffset());
                    trackSkipTimes = 0;
                } catch (DecoderException e) {
                    if (trackSkipTimes >= 5) {
                        logger.print("Too many skip attempts. Stopping streamer");
                        return;
                    }
                    logger.print("Track couldn't be decoded. Will skip it.");
                    stream.skipMilliseconds(trackItem.getTimeRemainder());
                    trackSkipTimes ++;
                }

                logger.print("---- PLAYER STOP  ----");

            } catch (Exception e) {
                // Terminate streamer on any exception
                logger.exception(e);
                return;
            }

            firstPlayingTrack = false;

        }


    }

}
