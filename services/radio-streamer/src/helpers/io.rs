use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::channel::mpsc;
use futures::channel::oneshot;
use futures::io::{Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt, SinkExt, StreamExt, TryFutureExt};
use futures_lite::FutureExt;
use slog::{debug, error, Logger};
use std::cmp::max;
use std::time::{Duration, Instant};

const BUFFER_SIZE: usize = 4096;

const THROTTLE_DURATION_MS: Duration = Duration::from_millis(50);

pub async fn send_from_stdout<'a>(
    stdout: &'a mut ChildStdout,
    sender: &'a mut mpsc::Sender<Result<Bytes, Error>>,
    logger: Logger,
) {
    let mut input_buffer = vec![0u8; BUFFER_SIZE];

    loop {
        match stdout.read(&mut input_buffer).await {
            Ok(read_bytes) => {
                if read_bytes == 0 {
                    debug!(logger, "Reached the end of the stdout stream");
                    break;
                }
                if let Err(error) = sender
                    .send(Ok(Bytes::copy_from_slice(&input_buffer[..read_bytes])))
                    .await
                {
                    error!(logger, "Unable to send bytes to sender"; "error" => ?error);
                    break;
                }
            }
            Err(error) => {
                error!(logger, "Error occurred on reading from stdout"; "error" => ?error);
                if let Err(error) = sender
                    .send(Err(Error::new(ErrorKind::Interrupted, error)))
                    .await
                {
                    error!(logger, "Unable to send error to sender"; "error" => ?error);
                }
                break;
            }
        }
    }
}

pub async fn read_to_stdin<'a>(
    receiver: &'a mut mpsc::Receiver<Result<Bytes, Error>>,
    stdin: &'a mut ChildStdin,
    logger: Logger,
) {
    while let Some(r) = receiver.next().await {
        match r {
            Ok(bytes) => {
                if let Err(error) = stdin.write(&bytes[..]).await {
                    error!(logger, "Unable to write bytes to stdin"; "error" => ?error);
                    break;
                }
            }
            Err(error) => {
                error!(logger, "Unable to read bytes from receiver"; "error" => ?error);
                break;
            }
        };
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
            .map_err(|err| PipeChannelError::SendError(err))
            .await
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
