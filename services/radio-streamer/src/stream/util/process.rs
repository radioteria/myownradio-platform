use actix_web::web::Bytes;
use async_process::{ChildStdin, ChildStdout};
use futures::io::{BufReader, Error, ErrorKind};
use futures::{AsyncReadExt, AsyncWriteExt};
use std::process::{Command, Output};
use std::time::Duration;

const READ_FROM_STDOUT_TIMEOUT: Duration = Duration::from_secs(5);
const WRITE_TO_STDOUT_TIMEOUT: Duration = Duration::from_secs(5);

/// Reads data from `stdout` and stores it in `buffer`.
/// Returns the number of bytes read, or an error if the read operation fails or times out.
pub(crate) async fn read_from_stdout(
    stdout: &mut BufReader<ChildStdout>,
    mut buffer: &mut Vec<u8>,
) -> Result<usize, Error> {
    match actix_rt::time::timeout(READ_FROM_STDOUT_TIMEOUT, stdout.read(&mut buffer)).await {
        Ok(Ok(len)) => Ok(len),
        Ok(Err(error)) => Err(Error::from(error)),
        Err(_) => Err(Error::from(ErrorKind::TimedOut)),
    }
}

/// Writes `bytes` to `stdin`.
/// Returns an error if the write operation fails or times out.
pub(crate) async fn write_to_stdin(stdin: &mut ChildStdin, bytes: Bytes) -> Result<(), Error> {
    match actix_rt::time::timeout(WRITE_TO_STDOUT_TIMEOUT, stdin.write(&bytes[..])).await {
        Ok(Ok(_)) => Ok(()),
        Ok(Err(error)) => Err(error),
        Err(_) => Err(Error::from(ErrorKind::TimedOut)),
    }
}

/// Runs the `which` command to find the path of the specified command.
/// Returns `None` if the command is not found.
pub(crate) fn which(command: &str) -> Option<String> {
    let Output { stdout, status, .. } = Command::new("which").args(&[command]).output().unwrap();

    if !status.success() {
        return None;
    }

    Some(String::from_utf8(stdout).unwrap().trim().to_string())
}
