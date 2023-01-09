use actix_rt::time::Instant;
use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::channel::oneshot;
use futures::io::{Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt};
use futures_lite::FutureExt;
use std::time::Duration;

const READ_FROM_STDOUT_TIMEOUT: Duration = Duration::from_secs(10);

pub async fn read_from_stdout<'a>(
    stdout: &'a mut ChildStdout,
    read_buffer: &'a mut Vec<u8>,
) -> Option<Result<Bytes, Error>> {
    match actix_rt::time::timeout(READ_FROM_STDOUT_TIMEOUT, stdout.read(read_buffer)).await {
        Ok(Ok(read_bytes)) => {
            if read_bytes == 0 {
                return None;
            }

            Some(Ok(Bytes::copy_from_slice(&read_buffer[..read_bytes])))
        }
        Ok(Err(error)) => Some(Err(Error::from(error))),
        Err(_) => Some(Err(Error::from(ErrorKind::TimedOut))),
    }
}

pub async fn read_exact_from_stdout(
    stdout: &mut ChildStdout,
    size: &usize,
) -> Option<Result<Bytes, Error>> {
    let mut buffer = vec![0u8; *size];

    match actix_rt::time::timeout(READ_FROM_STDOUT_TIMEOUT, stdout.read_exact(&mut buffer)).await {
        Ok(Ok(())) => Some(Ok(Bytes::copy_from_slice(&buffer[..]))),
        Ok(Err(error)) => Some(Err(Error::from(error))),
        Err(_) => Some(Err(Error::from(ErrorKind::TimedOut))),
    }
}

pub async fn write_to_stdin(stdin: &mut ChildStdin, bytes: Bytes) -> Result<(), Error> {
    match stdin.write(&bytes[..]).await {
        Ok(_) => Ok(()),
        Err(error) => Err(error),
    }
}

pub async fn sleep_until_deadline(
    deadline: &Instant,
    cancel_receiver: &mut oneshot::Receiver<()>,
) -> Result<(), oneshot::Canceled> {
    let deadline = deadline.clone();
    let pipe_future = async { Ok(actix_rt::time::sleep_until(deadline).await) };

    pipe_future.or(cancel_receiver).await
}
