use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::stream::ffmpeg_decoder::{make_ffmpeg_decoder, DecoderError};
use crate::stream::types::TimedBuffer;
use futures::channel::mpsc;
use futures::{SinkExt, StreamExt};
use slog::{debug, error, Logger};
use std::sync::Mutex;
use std::time::Duration;

const ALLOWED_DELAY: Duration = Duration::from_secs(1);

#[derive(Debug)]
pub(crate) enum PlayerLoopError {
    ChannelNotFound,
    DecoderError(DecoderError),
    BackendClientError(MorBackendClientError),
}

#[derive(Debug)]
pub(crate) enum PlayerLoopEvent {
    TimedBuffer(TimedBuffer),
    TitleChange(String),
}

pub(crate) async fn make_player_loop(
    channel_id: &usize,
    client_id: &Option<String>,
    path_to_ffmpeg: &str,
    backend_client: &BackendClient,
    logger: &Logger,
) -> Result<mpsc::Receiver<PlayerLoopEvent>, PlayerLoopError> {
    let logger = logger.clone();
    let client_id = client_id.clone();

    let (tx, rx) = mpsc::channel(0);

    let channel_info = match backend_client
        .get_channel_info(&channel_id, client_id.clone())
        .await
    {
        Ok(channel_info) => channel_info,
        Err(MorBackendClientError::ChannelNotFound) => {
            return Err(PlayerLoopError::ChannelNotFound);
        }
        Err(error) => {
            error!(logger, "Unable to get channel info"; "error" => ?error);
            return Err(PlayerLoopError::BackendClientError(error));
        }
    };

    actix_rt::spawn({
        let logger = logger.clone();
        let stored_next_track_decoder: Mutex<Option<_>> = Mutex::default();

        let mut tx = tx;

        async move {
            loop {
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
                        path_to_ffmpeg,
                        &backend_client,
                        &logger,
                    ) {
                        Ok(track_decoder) => track_decoder,
                        Err(error) => {
                            error!(logger, "Unable create track decoder"; "error" => ?error);
                            return;
                        }
                    },
                };

                let next_track_decoder = match make_ffmpeg_decoder(
                    &now_playing.next_track.url,
                    &Duration::from_secs(0),
                    path_to_ffmpeg,
                    &backend_client,
                    &logger,
                ) {
                    Ok(next_track_decoder) => next_track_decoder,
                    Err(error) => {
                        error!(logger, "Unable create next track decoder"; "error" => ?error);
                        return;
                    }
                };

                stored_next_track_decoder
                    .lock()
                    .expect("Unable to obtain lock on replacing stored next track decoder")
                    .replace(next_track_decoder);

                let title = now_playing.current_track.title.clone();

                if let Err(error) = tx.send(PlayerLoopEvent::TitleChange(title)).await {
                    debug!(logger, "Stopping player loop: channel closed");
                }

                while let Some(buffer) = track_decoder.next().await {
                    // TODO
                }
            }
        }
    });

    Ok(rx)
}
