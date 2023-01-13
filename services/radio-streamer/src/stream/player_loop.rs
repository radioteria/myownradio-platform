use crate::backend_client::{BackendClient, MorBackendClientError, NowPlaying};
use crate::helpers::io::sleep_until_deadline;
use crate::metrics::Metrics;
use crate::stream::types::Buffer;
use crate::stream::{build_ffmpeg_decoder, DecoderOutput};
use actix_rt::time::Instant;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use scopeguard::defer;
use slog::{debug, error, info, trace, Logger};
use std::time::{Duration, SystemTime};

const ALLOWED_DELAY: Duration = Duration::from_secs(1);
const LAST_PRELOAD_TIME: Duration = Duration::from_millis(2500);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    DecodedBuffer(Buffer),
    TrackTitle(String),
    RestartSender(oneshot::Sender<()>),
}

pub(crate) fn make_player_loop(
    channel_id: &usize,
    backend_client: &BackendClient,
    logger: &Logger,
    metrics: &Metrics,
) -> mpsc::Receiver<PlayerLoopMessage> {
    let channel_id = channel_id.clone();

    let logger = logger.clone();
    let metrics = metrics.clone();

    let (tx, rx) = mpsc::channel(0);

    actix_rt::spawn({
        let backend_client = backend_client.clone();
        let logger = logger.clone();

        let mut tx = tx;

        async move {
            metrics.inc_active_player_loops();

            defer!(metrics.dec_active_player_loops());

            info!(logger, "Starting player loop"; "channel_id" => &channel_id);

            defer!(info!(logger, "Stopping player loop"; "channel_id" => &channel_id););

            let stream_start_time = SystemTime::now() - LAST_PRELOAD_TIME;
            let stream_instant_time = Instant::now() - LAST_PRELOAD_TIME;

            trace!(logger, "Stream initial clock"; "start_time" => ?stream_start_time, "instant_time" => ?stream_instant_time);

            let mut offset_pts = Duration::from_secs(0);

            loop {
                let elapsed_time = stream_start_time + offset_pts;
                trace!(logger, "Elapsed stream time"; "time" => ?elapsed_time);

                let now_playing = match backend_client
                    .get_now_playing(&channel_id, &elapsed_time)
                    .await
                {
                    Ok(now_playing) => now_playing,
                    Err(MorBackendClientError::ChannelNotFound) => {
                        // Channel was deleted when streaming. Exit loop.
                        break;
                    }
                    Err(error) => {
                        error!(logger, "Unable to get now playing"; "error" => ?error);
                        break;
                    }
                };

                let NowPlaying {
                    current_track,
                    next_track,
                    ..
                } = now_playing;

                let (title, url, offset) = {
                    let left_offset = current_track.offset;
                    let right_offset = current_track.duration - current_track.offset;

                    if right_offset < ALLOWED_DELAY {
                        (next_track.title, next_track.url, Duration::default())
                    } else if left_offset < ALLOWED_DELAY {
                        (current_track.title, current_track.url, Duration::default())
                    } else {
                        (current_track.title, current_track.url, left_offset)
                    }
                };

                let mut track_decoder = match build_ffmpeg_decoder(&url, &offset, &logger, &metrics)
                {
                    Ok(track_decoder) => track_decoder,
                    Err(error) => {
                        error!(logger, "Unable create track decoder"; "error" => ?error);
                        return;
                    }
                };

                if let Err(_) = tx.send(PlayerLoopMessage::TrackTitle(title)).await {
                    debug!(
                        logger,
                        "Stopping player loop: channel closed on updating title"
                    );
                    return;
                }

                let (restart_tx, mut restart_rx) = oneshot::channel::<()>();

                if let Err(_) = tx.send(PlayerLoopMessage::RestartSender(restart_tx)).await {
                    debug!(
                        logger,
                        "Stopping player loop: channel closed on updating restart sender"
                    );
                    return;
                }

                let mut stream_pts = offset_pts;

                while let Some(DecoderOutput::Buffer(buffer)) = track_decoder.next().await {
                    stream_pts = offset_pts + *buffer.dts();

                    if let Err(_) =
                        sleep_until_deadline(&(stream_instant_time + stream_pts), &mut restart_rx)
                            .await
                    {
                        debug!(logger, "Sleep cancelled");
                        break;
                    }

                    if let Ok(Some(())) = restart_rx.try_recv() {
                        debug!(logger, "Exit current track loop on restart signal");
                        break;
                    }

                    if buffer.is_empty() {
                        break;
                    }

                    if let Err(_) = tx
                        .send(PlayerLoopMessage::DecodedBuffer(Buffer::new(
                            buffer.bytes().clone(),
                            buffer.dts().clone(),
                        )))
                        .await
                    {
                        debug!(
                            logger,
                            "Stopping player loop: channel closed on sending buffer"
                        );
                        return;
                    }
                }

                offset_pts = stream_pts;
            }
        }
    });

    rx
}
