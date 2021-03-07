use crate::helpers::io::send_from_stdout;
use actix_web::web::Bytes;
use async_process::{Command, Stdio};
use futures::channel::mpsc;
use futures::io;
use slog::{debug, error, Logger};

#[derive(Debug)]
pub enum AudioDecoderError {
    ProcessError,
    NoStdout,
}

pub struct AudioDecoder {
    path_to_ffmpeg: String,
    logger: Logger,
}

impl AudioDecoder {
    pub fn new(path_to_ffmpeg: &str, logger: &Logger) -> Self {
        AudioDecoder {
            path_to_ffmpeg: path_to_ffmpeg.to_string(),
            logger: logger.clone(),
        }
    }

    pub fn decode_audio_file(
        &self,
        url: &str,
        offset: &u32,
    ) -> Result<mpsc::Receiver<Result<Bytes, io::Error>>, AudioDecoderError> {
        let (sender, receiver) = mpsc::channel(4);

        debug!(self.logger, "Spawning audio decoder...");

        let offset = format!("{:.4}", offset / 1000);

        let child = match Command::new(&self.path_to_ffmpeg)
            .args(&[
                "-re",
                "-fflags",
                "fastseek",
                "-ss",
                &offset,
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

        debug!(self.logger, "Audio decoder spawned");

        let stdout = match child.stdout {
            Some(stdout) => stdout,
            None => {
                error!(self.logger, "Stdout is not available");
                return Err(AudioDecoderError::NoStdout);
            }
        };

        actix_rt::spawn({
            let logger = self.logger.clone();

            async move {
                send_from_stdout(stdout, sender, logger).await;
            }
        });

        Ok(receiver)
    }
}
