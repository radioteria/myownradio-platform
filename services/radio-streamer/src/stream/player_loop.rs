use crate::backend_client::{BackendClient, MorBackendClientError, NowPlaying};
use crate::metrics::Metrics;
use crate::stream::constants::REALTIME_STARTUP_BUFFER_TIME;
use crate::stream::types::{Buffer, TrackTitle};
use crate::stream::util::channels::TimedMessage;
use crate::stream::util::clock::MessageSyncClock;
use crate::stream::util::ffmpeg::{build_ffmpeg_decoder, DecoderError, DecoderOutput};
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use scopeguard::defer;
use slog::{debug, error, info, Logger};
use std::time::{Duration, SystemTime};

/// When the audio position is within this threshold from the start
/// of the current or next track, it is considered at zero.
const ZERO_OFFSET_THRESHOLD: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    DecodedBuffer(Buffer),
    TrackTitle(TrackTitle),
    RestartSender(oneshot::Sender<()>),
    Error(PlayerLoopError),
    EOF,
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum PlayerLoopError {
    #[error(transparent)]
    BackendError(#[from] MorBackendClientError),
    #[error(transparent)]
    DecoderError(#[from] DecoderError),
    #[error(transparent)]
    SendError(#[from] mpsc::SendError),
    #[error("The decoder stopped unexpectedly with an exit code = {0}")]
    DecoderUnexpectedTermination(i32),
}

impl TimedMessage for &Buffer {
    fn pts(&self) -> &Duration {
        self.pts_hint()
    }
}

async fn run_loop(
    channel_id: usize,
    backend_client: BackendClient,
    logger: Logger,
    metrics: Metrics,
    mut player_loop_msg_sender: mpsc::Sender<PlayerLoopMessage>,
) -> Result<(), PlayerLoopError> {
    let mut sync_clock = MessageSyncClock::init(SystemTime::now() - REALTIME_STARTUP_BUFFER_TIME);

    info!(logger, "Starting player loop"; "channel_id" => &channel_id);
    metrics.inc_active_player_loops();

    defer!(info!(logger, "Stopping player loop"; "channel_id" => &channel_id););
    defer!(metrics.dec_active_player_loops());

    loop {
        let now_playing = backend_client
            .get_now_playing(&channel_id, &sync_clock.elapsed())
            .await?;

        let NowPlaying {
            current_track,
            next_track,
            ..
        } = now_playing;

        let (title, url, offset) = {
            let left_offset = current_track.offset;
            let right_offset = current_track.duration - current_track.offset;

            if right_offset < ZERO_OFFSET_THRESHOLD {
                (next_track.title, next_track.url, Duration::default())
            } else if left_offset < ZERO_OFFSET_THRESHOLD {
                (current_track.title, current_track.url, Duration::default())
            } else {
                (current_track.title, current_track.url, left_offset)
            }
        };

        let mut track_decoder = build_ffmpeg_decoder(&url, &offset, &logger, &metrics)?;

        player_loop_msg_sender
            .send(PlayerLoopMessage::TrackTitle(TrackTitle::new(
                title,
                *sync_clock.position(),
                *sync_clock.position(),
            )))
            .await?;

        let (restart_tx, mut restart_rx) = oneshot::channel::<()>();

        player_loop_msg_sender
            .send(PlayerLoopMessage::RestartSender(restart_tx))
            .await?;

        while let Some(msg) = track_decoder.next().await {
            match msg {
                DecoderOutput::Buffer(buffer) => {
                    sync_clock.wait(&buffer).await;

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
                DecoderOutput::EOF => {
                    break;
                }
                DecoderOutput::Error(exit_code) => {
                    return Err(PlayerLoopError::DecoderUnexpectedTermination(exit_code));
                }
            }
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

    let (mut player_loop_msg_sender, player_loop_msg_receiver) = mpsc::channel(0);

    actix_rt::spawn(async move {
        match run_loop(
            channel_id,
            backend_client,
            logger.clone(),
            metrics,
            player_loop_msg_sender.clone(),
        )
        .await
        {
            Ok(_) | Err(PlayerLoopError::BackendError(MorBackendClientError::ChannelNotFound)) => {
                let _ = player_loop_msg_sender.send(PlayerLoopMessage::EOF).await;
            }
            Err(error) => {
                let _ = player_loop_msg_sender
                    .send(PlayerLoopMessage::Error(error))
                    .await;
            }
        }
    });

    player_loop_msg_receiver
}
