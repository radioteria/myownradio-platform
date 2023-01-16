use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::channel::oneshot;
use futures::io::{BufReader, Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt};
use futures_lite::FutureExt;
use std::time::Duration;

const READ_FROM_STDOUT_TIMEOUT: Duration = Duration::from_secs(10);

pub async fn read_from_stdout(
    stdout: &mut BufReader<ChildStdout>,
    mut buffer: &mut Vec<u8>,
) -> Result<usize, Error> {
    match actix_rt::time::timeout(READ_FROM_STDOUT_TIMEOUT, stdout.read(&mut buffer)).await {
        Ok(Ok(len)) => Ok(len),
        Ok(Err(error)) => Err(Error::from(error)),
        Err(_) => Err(Error::from(ErrorKind::TimedOut)),
    }
}

pub async fn write_to_stdin(stdin: &mut ChildStdin, bytes: Bytes) -> Result<(), Error> {
    match stdin.write(&bytes[..]).await {
        Ok(_) => Ok(()),
        Err(error) => Err(error),
    }
}

pub async fn sleep(
    duration: &Duration,
    cancel_receiver: &mut oneshot::Receiver<()>,
) -> Result<(), oneshot::Canceled> {
    let duration = duration.clone();
    let pipe_future = async { Ok(actix_rt::time::sleep(duration).await) };

    pipe_future.or(cancel_receiver).await
}
