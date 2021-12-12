use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::helpers::io::sleep_until_deadline;
use crate::metrics::Metrics;
use crate::stream::constants::AUDIO_BYTES_PER_SECOND;
use crate::stream::ffmpeg_decoder::make_ffmpeg_decoder;
use crate::stream::types::{DecodedBuffer, TimedBuffer};
use actix_rt::task::JoinHandle;
use actix_rt::time::Instant;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use scopeguard::defer;
use slog::{debug, error, info, Logger};
use std::sync::{Arc, Mutex};
use std::time::Duration;

const ALLOWED_DELAY: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    TimedBuffer(TimedBuffer),
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
        let base_time = Instant::now();

        let mut tx = tx;

        async move {
            metrics.inc_active_player_loops();

            defer!(metrics.dec_active_player_loops());

            info!(logger, "Starting player loop"; "channel_id" => &channel_id);

            defer!(info!(logger, "Stopping player loop"; "channel_id" => &channel_id););

            let mut bytes_sent = 0usize;

            loop {
                if let Some(future) = next_track_future.lock().unwrap().take() {
                    future.abort();
                }

                let now_playing = match backend_client
                    .get_now_playing(&channel_id, client_id.clone(), &Duration::from_secs(0))
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
                let time = Instant::now();

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

                while let Some(DecodedBuffer(bytes, bytes_offset)) = track_decoder.next().await {
                    let deadline = time + bytes_offset;

                    if let Err(_) = sleep_until_deadline(deadline, &mut restart_rx).await {
                        debug!(logger, "Sleep cancelled");
                    }

                    if let Ok(Some(())) = restart_rx.try_recv() {
                        debug!(logger, "Exit current track loop on restart signal");
                        break;
                    }

                    let bytes_len = bytes.len();
                    let decoding_time_seconds = bytes_sent as f64 / AUDIO_BYTES_PER_SECOND as f64;
                    let decoding_time = Duration::from_secs_f64(decoding_time_seconds);
                    let time = base_time + decoding_time;

                    if let Err(_) = tx
                        .send(PlayerLoopMessage::TimedBuffer(TimedBuffer(bytes, time)))
                        .await
                    {
                        debug!(
                            logger,
                            "Stopping player loop: channel closed on sending buffer"
                        );
                        return;
                    }

                    bytes_sent += bytes_len;
                }
            }
        }
    });

    rx
}
