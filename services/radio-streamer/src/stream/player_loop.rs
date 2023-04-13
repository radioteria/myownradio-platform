use crate::backend_client::{BackendClient, MorBackendClientError, NowPlaying};
use crate::metrics::Metrics;
use crate::stream::constants::REALTIME_STARTUP_BUFFER_TIME;
use crate::stream::types::{Buffer, TrackTitle};
use crate::stream::util::channels::TimedMessage;
use crate::stream::util::clock::MessageSyncClock;
use crate::stream::util::time::subtract_abs;
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, StreamExt};
use myownradio_ffmpeg_utils::{decode_audio_file, generate_silence, AudioDecoderError, Frame};
use scopeguard::defer;
use slog::{debug, error, info, warn, Logger};
use std::time::{Duration, SystemTime};

/// When the audio position is within this threshold from the start
/// of the current or next track, it is considered at zero.
const ZERO_OFFSET_THRESHOLD: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopMessage {
    Frame(Frame),
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
    DecoderError(#[from] AudioDecoderError),
    #[error(transparent)]
    SendError(#[from] mpsc::SendError),
    #[error("The decoder stopped unexpectedly with an exit code = {0}")]
    DecoderUnexpectedTermination(i32),
}

impl TimedMessage for &Buffer {
    fn message_pts(&self) -> Duration {
        self.pts_hint().clone()
    }
}

impl TimedMessage for &Frame {
    fn message_pts(&self) -> Duration {
        self.pts().into()
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

    'player: loop {
        let now_playing = backend_client
            .get_now_playing(&channel_id, &sync_clock.elapsed())
            .await?;

        let NowPlaying {
            current_track,
            next_track,
            ..
        } = now_playing;

        let position_before_decoder = *sync_clock.position();
        let (title, url, offset, duration) = {
            let left_offset = current_track.offset;
            let right_offset = current_track.duration - current_track.offset;

            if right_offset < ZERO_OFFSET_THRESHOLD {
                (
                    next_track.title,
                    next_track.url,
                    Duration::default(),
                    next_track.duration,
                )
            } else if left_offset < ZERO_OFFSET_THRESHOLD {
                (
                    current_track.title,
                    current_track.url,
                    Duration::default(),
                    current_track.duration,
                )
            } else {
                (
                    current_track.title,
                    current_track.url,
                    left_offset,
                    current_track.duration,
                )
            }
        };
        let remaining_time = duration - offset;

        player_loop_msg_sender
            .send(PlayerLoopMessage::TrackTitle(TrackTitle::new(
                title,
                *sync_clock.position(),
            )))
            .await?;

        let mut track_decoder = decode_audio_file(&url, &offset)?;

        let (restart_tx, mut restart_rx) = oneshot::channel::<()>();

        player_loop_msg_sender
            .send(PlayerLoopMessage::RestartSender(restart_tx))
            .await?;

        sync_clock.reset_next_pts();

        while let Some(frame) = track_decoder.next().await {
            sync_clock.wait(&frame).await;

            if let Ok(Some(())) = restart_rx.try_recv() {
                debug!(logger, "Aborting current track playback on restart signal");
                continue 'player;
            }

            if frame.is_empty() {
                break;
            }

            player_loop_msg_sender
                .send(PlayerLoopMessage::Frame(frame))
                .await?;
        }

        let position_after_decoder = *sync_clock.position();
        let decoded_time = subtract_abs(position_after_decoder, position_before_decoder);

        let diff = subtract_abs(remaining_time, decoded_time);
        if diff > ZERO_OFFSET_THRESHOLD {
            // @todo Get the reason why the track decoder exited early.
            warn!(
                logger,
                "Track decoder exited early: filling time gap with silence";
                "dur" => ?diff,
            );

            let mut silence_stream = generate_silence(Some(&diff));

            sync_clock.reset_next_pts();

            while let Some(frame) = silence_stream.next().await {
                sync_clock.wait(&frame).await;

                if let Ok(Some(())) = restart_rx.try_recv() {
                    debug!(logger, "Exit current track loop on restart signal");
                    break;
                }

                if frame.is_empty() {
                    break;
                }

                player_loop_msg_sender
                    .send(PlayerLoopMessage::Frame(frame))
                    .await?;
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
