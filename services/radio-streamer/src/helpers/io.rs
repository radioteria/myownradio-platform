use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::channel::mpsc::{Receiver, Sender};
use futures::io::{Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt, SinkExt, StreamExt};
use slog::{debug, error, Logger};

const BUFFER_SIZE: usize = 4096;

pub async fn send_from_stdout(
    mut stdout: ChildStdout,
    mut sender: Sender<Result<Bytes, Error>>,
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

pub async fn read_to_stdin(
    mut receiver: Receiver<Result<Bytes, Error>>,
    mut stdin: ChildStdin,
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
