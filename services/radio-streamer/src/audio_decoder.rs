use actix_web::web::Bytes;
use async_process::{Command, Stdio};
use envy::Error;
use futures::channel::mpsc;
use futures::{io, AsyncReadExt, SinkExt};
use slog::{error, Logger};
use std::io::ErrorKind;

#[derive(Debug)]
pub enum AudioDecoderError {
    ProcessError,
    NoStdout,
}

pub struct AudioDecoder {
    path_to_ffmpeg: String,
    path_to_ffprobe: String,
    logger: Logger,
}

impl AudioDecoder {
    pub fn new(path_to_ffmpeg: &str, path_to_ffprobe: &str, logger: &Logger) -> Self {
        AudioDecoder {
            path_to_ffmpeg: path_to_ffmpeg.to_string(),
            path_to_ffprobe: path_to_ffprobe.to_string(),
            logger: logger.clone(),
        }
    }

    pub fn decode_audio_file(
        &self,
        url: &str,
        offset: &u32,
    ) -> Result<mpsc::Receiver<Result<Bytes, io::Error>>, AudioDecoderError> {
        let (sender, receiver) = mpsc::channel(4);

        let child = match Command::new(&self.path_to_ffmpeg)
            .args(&[
                "-re",
                "-fflags",
                "fastseek",
                "-ss",
                format!("{:.4}", offset / 1000).as_str(),
                "-i",
                &url,
                "-filter",
                "afade=t=in:st=0:d=1",
                "-codec:a",
                "pcm_s16le",
                "-ar",
                "44100",
                "-ac",
                "2",
                "-f",
                "s16le",
                "-",
            ])
            .stdout(Stdio::piped())
            .stderr(Stdio::null())
            .stdin(Stdio::null())
            .spawn()
        {
            Ok(process) => process,
            Err(error) => {
                error!(self.logger, "Unable to start process"; "error" => ?error);
                return Err(AudioDecoderError::ProcessError);
            }
        };

        let mut stdout = match child.stdout {
            Some(stdout) => stdout,
            None => {
                error!(self.logger, "Stdout is not available");
                return Err(AudioDecoderError::NoStdout);
            }
        };

        actix_rt::spawn({
            let mut sender = sender;
            let mut input_buffer = vec![0u8; 4096];
            let logger = self.logger.clone();

            async move {
                loop {
                    match stdout.read(&mut input_buffer).await {
                        Ok(read_bytes) => {
                            if read_bytes == 0 {
                                break;
                            }
                            if let Err(error) = sender
                                .send(Ok(Bytes::copy_from_slice(&input_buffer[..read_bytes])))
                                .await
                            {
                                error!(logger, "Unable to send bytes to Sender"; "error" => ?error);
                                break;
                            }
                        }
                        Err(error) => {
                            error!(logger, "Error occurred on reading stdout"; "error" => ?error);
                            if let Err(error) = sender
                                .send(Err(io::Error::new(ErrorKind::BrokenPipe, error)))
                                .await
                            {
                                error!(logger, "Unable to send error to Sender"; "error" => ?error);
                            }
                            break;
                        }
                    }
                }
            }
        });

        Ok(receiver)
    }
}
