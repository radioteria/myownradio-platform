use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::stream::ffmpeg_decoder::DecoderError;
use crate::stream::types::TimedBuffer;
use futures::channel::mpsc;

#[derive(Debug)]
pub(crate) enum PlayerLoopError {
    DecoderError(DecoderError),
    BackendClientError(MorBackendClientError),
}

#[derive(Debug)]
pub(crate) enum PlayerLoopEvent {
    TimedBuffer(TimedBuffer),
    TitleChange(String),
}

pub(crate) fn make_player_loop(
    backend_client: &BackendClient,
) -> Result<mpsc::Receiver<PlayerLoopEvent>, PlayerLoopError> {
    let (tx, rx) = mpsc::channel(0);

    Ok(rx)
}
