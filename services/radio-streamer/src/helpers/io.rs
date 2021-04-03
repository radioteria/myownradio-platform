use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::channel::{mpsc, oneshot};
use futures::io::{Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt, SinkExt, StreamExt};
use futures_lite::FutureExt;
use slog::{error, Logger};
use std::cmp::max;
use std::time::{Duration, Instant};

const THROTTLE_DURATION_MS: Duration = Duration::from_millis(50);

pub async fn read_from_stdout<'a>(
    stdout: &'a mut ChildStdout,
    read_buffer: &'a mut Vec<u8>,
) -> Option<Result<Bytes, Error>> {
    match stdout.read(read_buffer).await {
        Ok(read_bytes) => {
            if read_bytes == 0 {
                return None;
            }

            Some(Ok(Bytes::copy_from_slice(&read_buffer[..read_bytes])))
        }
        Err(error) => Some(Err(Error::from(error))),
    }
}

pub async fn write_to_stdin(stdin: &mut ChildStdin, bytes: Bytes) -> Result<(), Error> {
    match stdin.write(&bytes[..]).await {
        Ok(_) => Ok(()),
        Err(error) => Err(error),
    }
}

pub async fn pipe_channel<'a>(
    receiver: &'a mut mpsc::Receiver<Result<Bytes, Error>>,
    sender: &'a mut mpsc::Sender<Result<Bytes, Error>>,
) -> Result<(), mpsc::SendError> {
    while let Some(result) = receiver.next().await {
        sender.send(result).await?;
    }
    Ok(())
}

pub fn spawn_pipe_channel(
    receiver: mpsc::Receiver<Result<Bytes, Error>>,
    sender: mpsc::Sender<Result<Bytes, Error>>,
) {
    actix_rt::spawn({
        let mut receiver = receiver;
        let mut sender = sender;

        async move {
            let _ = pipe_channel(&mut receiver, &mut sender).await;
        }
    });
}

#[derive(Debug)]
pub enum PipeChannelError {
    SendError(mpsc::SendError),
    CancelError(oneshot::Canceled),
}

pub async fn pipe_channel_with_cancel<'a>(
    receiver: &'a mut mpsc::Receiver<Result<Bytes, Error>>,
    sender: &'a mut mpsc::Sender<Result<Bytes, Error>>,
    cancel_receiver: &'a mut oneshot::Receiver<()>,
) -> Result<(), PipeChannelError> {
    let pipe_future = async {
        pipe_channel(receiver, sender)
            .await
            .map_err(|err| PipeChannelError::SendError(err))
    };

    let cancel_future = async {
        cancel_receiver
            .await
            .map_err(|canceled| PipeChannelError::CancelError(canceled))
    };

    pipe_future.or(cancel_future).await
}

pub fn throttled_channel(
    bytes_per_second: usize,
    prefetch_size: usize,
) -> (
    mpsc::Sender<Result<Bytes, Error>>,
    mpsc::Receiver<Result<Bytes, Error>>,
) {
    let bytes_per_second = bytes_per_second as isize;
    let prefetch_size = prefetch_size as isize;

    let (input_tx, input_rx) = mpsc::channel::<Result<Bytes, Error>>(0);
    let (output_tx, output_rx) = mpsc::channel::<Result<Bytes, Error>>(0);

    actix_rt::spawn({
        let mut input_rx = input_rx;
        let mut output_tx = output_tx;

        let start = Instant::now();

        async move {
            let mut bytes_transferred = -prefetch_size;

            while let Some(Ok(r)) = input_rx.next().await {
                bytes_transferred += r.len() as isize;

                let actual_bytes_per_second =
                    bytes_transferred / max(start.elapsed().as_secs() as isize, 1isize);

                if actual_bytes_per_second > bytes_per_second {
                    actix_rt::time::sleep(THROTTLE_DURATION_MS).await;
                }

                if let Err(_) = output_tx.send(Ok(r)).await {
                    break;
                }
            }
        }
    });

    (input_tx, output_rx)
}
