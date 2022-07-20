use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::helpers::io::sleep_until_deadline;
use crate::metrics::Metrics;
use crate::stream::ffmpeg_decoder::make_ffmpeg_decoder;
use crate::stream::types::Buffer;
use actix_rt::task::JoinHandle;
use actix_rt::time::Instant;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use scopeguard::defer;
use slog::{debug, error, info, trace, Logger};
use std::sync::{Arc, Mutex};
use std::time::{Duration, SystemTime};

const ALLOWED_DELAY: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    DecodedBuffer(Buffer),
    TrackTitle(String),
    RestartSender(oneshot::Sender<()>),
}

pub(crate) fn make_player_loop(
    channel_id: &usize,
    client_id: &Option<String>,
    path_to_ffmpeg: &str,
    backend_client: &BackendClient,
    logger: &Logger,
    metrics: &Metrics,
) -> mpsc::Receiver<PlayerLoopMessage> {
    let client_id = client_id.clone();
    let channel_id = channel_id.clone();
    let path_to_ffmpeg = path_to_ffmpeg.to_owned();

    let logger = logger.clone();
    let metrics = metrics.clone();

    let (tx, rx) = mpsc::channel(0);

    actix_rt::spawn({
        let backend_client = backend_client.clone();
        let logger = logger.clone();

        let stored_next_track_decoder: Arc<Mutex<Option<_>>> = Arc::default();
        let next_track_future: Mutex<Option<JoinHandle<_>>> = Mutex::default();

        let mut tx = tx;

        async move {
            metrics.inc_active_player_loops();

            defer!(metrics.dec_active_player_loops());

            info!(logger, "Starting player loop"; "channel_id" => &channel_id);

            defer!(info!(logger, "Stopping player loop"; "channel_id" => &channel_id););

            let stream_start_time = SystemTime::now();
            let stream_instant_time = Instant::now();

            trace!(logger, "Stream initial clock"; "start_time" => ?stream_start_time, "instant_time" => ?stream_instant_time);

            let mut offset_pts = Duration::from_secs(0);

            loop {
                if let Some(future) = next_track_future.lock().unwrap().take() {
                    debug!(logger, "Cancelling preloaded next track decoder");
                    future.abort();
                }

                let elapsed_time = stream_start_time + offset_pts;
                trace!(logger, "Elapsed stream time"; "time" => ?elapsed_time);

                let now_playing = match backend_client
                    .get_now_playing(&channel_id, client_id.clone(), &elapsed_time)
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

                let mut track_decoder = match stored_next_track_decoder
                    .lock()
                    .expect("Unable to obtain lock on reading stored next track decoder")
                    .take()
                {
                    Some(track_decoder) if now_playing.current_track.offset < ALLOWED_DELAY => {
                        track_decoder
                    }
                    _ => match make_ffmpeg_decoder(
                        &now_playing.current_track.url,
                        &now_playing.current_track.offset,
                        &path_to_ffmpeg,
                        &logger,
                        &metrics,
                    ) {
                        Ok(track_decoder) => track_decoder,
                        Err(error) => {
                            error!(logger, "Unable create track decoder"; "error" => ?error);
                            return;
                        }
                    },
                };

                next_track_future.lock().unwrap().replace(actix_rt::spawn({
                    let logger = logger.clone();
                    let metrics = metrics.clone();
                    let next_track_url = now_playing.next_track.url.clone();
                    let stored_next_track_decoder = stored_next_track_decoder.clone();
                    let path_to_ffmpeg = path_to_ffmpeg.clone();

                    async move {
                        let next_track_decoder = match make_ffmpeg_decoder(
                            &next_track_url,
                            &Duration::from_secs(0),
                            &path_to_ffmpeg,
                            &logger,
                            &metrics,
                        ) {
                            Ok(next_track_decoder) => next_track_decoder,
                            Err(error) => {
                                error!(logger, "Unable create next track decoder"; "error" => ?error);
                                return;
                            }
                        };

                        stored_next_track_decoder
                            .lock()
                            .unwrap()
                            .replace(next_track_decoder);
                    }
                }));

                let title = now_playing.current_track.title.clone();

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

                while let Some(buffer) = track_decoder.next().await {
                    stream_pts = offset_pts + *buffer.dts();

                    if let Err(_) =
                        sleep_until_deadline(&(stream_instant_time + stream_pts), &mut restart_rx)
                            .await
                    {
                        debug!(logger, "Sleep cancelled");
                    }

                    if let Ok(Some(())) = restart_rx.try_recv() {
                        debug!(logger, "Exit current track loop on restart signal");
                        break;
                    }

                    trace!(
                        logger,
                        "Received buffer from decoder";
                        "len" => buffer.bytes().len(),
                        "buff_dts" => buffer.dts().as_millis(),
                        "buff_pts" => buffer.pts().as_millis(),
                        "stream_pts" => stream_pts.as_millis()
                    );

                    if buffer.is_empty() {
                        break;
                    }

                    if let Err(_) = tx
                        .send(PlayerLoopMessage::DecodedBuffer(Buffer::new(
                            buffer.bytes().clone(),
                            buffer.dts().clone(),
                            stream_pts.clone(),
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
