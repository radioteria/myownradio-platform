use crate::backend_client::{BackendClient, MorBackendClientError, NowPlaying};
use crate::helpers::io::sleep;
use crate::metrics::Metrics;
use crate::stream::constants::PRELOAD_TIME;
use crate::stream::ffmpeg::DecoderError;
use crate::stream::types::Buffer;
use crate::stream::{build_ffmpeg_decoder, DecoderOutput};
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use scopeguard::defer;
use slog::{debug, error, info, trace, Logger};
use std::time::{Duration, SystemTime};

const ALLOWED_DELAY: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    DecodedBuffer(Buffer),
    TrackTitle(String),
    RestartSender(oneshot::Sender<()>),
}

#[derive(thiserror::Error, Debug)]
enum PlayerLoopError {
    #[error(transparent)]
    BackendError(#[from] MorBackendClientError),
    #[error(transparent)]
    DecoderError(#[from] DecoderError),
    #[error(transparent)]
    SendError(#[from] mpsc::SendError),
}

async fn run_loop(
    channel_id: usize,
    backend_client: BackendClient,
    logger: Logger,
    metrics: Metrics,
    mut player_loop_msg_sender: mpsc::Sender<PlayerLoopMessage>,
) -> Result<(), PlayerLoopError> {
    info!(logger, "Starting player loop"; "channel_id" => &channel_id);
    metrics.inc_active_player_loops();

    defer!(info!(logger, "Stopping player loop"; "channel_id" => &channel_id););
    defer!(metrics.dec_active_player_loops());

    let stream_started_at = SystemTime::now() - PRELOAD_TIME;

    trace!(logger, "Stream initial clock"; "stream_started_at" => ?stream_started_at);

    let mut dts_offset = Duration::from_secs(0);

    loop {
        let elapsed_time = stream_started_at + dts_offset;
        trace!(logger, "Elapsed stream time"; "time" => ?elapsed_time);

        let now_playing = backend_client
            .get_now_playing(&channel_id, &elapsed_time)
            .await?;

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

        let mut track_decoder = build_ffmpeg_decoder(&url, &offset, &logger, &metrics)?;

        player_loop_msg_sender
            .send(PlayerLoopMessage::TrackTitle(title))
            .await?;

        let (restart_tx, mut restart_rx) = oneshot::channel::<()>();

        player_loop_msg_sender
            .send(PlayerLoopMessage::RestartSender(restart_tx))
            .await?;

        let mut previous_packet_dts = Duration::from_secs(0);
        while let Some(DecoderOutput::Buffer(buffer)) = track_decoder.next().await {
            let buffer_dts = *buffer.dts_hint();
            let buffer_dur = buffer_dts - previous_packet_dts;

            dts_offset += buffer_dur;
            previous_packet_dts = buffer_dts;

            let sleep_dur = (stream_started_at + dts_offset)
                .duration_since(SystemTime::now())
                .ok();

            if let Some(duration) = sleep_dur {
                if let Err(_) = sleep(&duration, &mut restart_rx).await {
                    debug!(logger, "Sleep cancelled");
                    break;
                }
            }

            if let Ok(Some(())) = restart_rx.try_recv() {
                debug!(logger, "Exit current track loop on restart signal");
                break;
            }

            if buffer.is_empty() {
                break;
            }

            player_loop_msg_sender
                .send(PlayerLoopMessage::DecodedBuffer(Buffer::new(
                    buffer.bytes().clone(),
                    buffer.pts_hint().clone(),
                    buffer.dts_hint().clone(),
                )))
                .await?;
        }
    }
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
    let backend_client = backend_client.clone();

    let (player_loop_msg_sender, player_loop_msg_receiver) = mpsc::channel(0);

    actix_rt::spawn(async move {
        if let Err(error) = run_loop(
            channel_id,
            backend_client,
            logger.clone(),
            metrics,
            player_loop_msg_sender,
        )
        .await
        {
            match error {
                PlayerLoopError::BackendError(MorBackendClientError::ChannelNotFound) => (),
                error => {
                    error!(logger, "Error happened in player loop"; "error" => ?error);
                }
            }
        }
    });

    player_loop_msg_receiver
}
