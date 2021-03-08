use crate::audio_formats::AudioFormat;
use crate::helpers::io::{read_to_stdin, send_from_stdout};
use actix_web::web::Bytes;
use async_process::{Command, Stdio};
use futures::channel::{mpsc, oneshot};
use futures::io;
use futures_lite::FutureExt;
use slog::{debug, error, Logger};

#[derive(Debug)]
pub enum AudioEncoderError {
    ProcessError,
    StdoutUnavailable,
    StdinUnavailable,
}

pub struct AudioEncoder {
    path_to_ffmpeg: String,
    logger: Logger,
}

impl AudioEncoder {
    pub fn new(path_to_ffmpeg: &str, logger: &Logger) -> Self {
        AudioEncoder {
            path_to_ffmpeg: path_to_ffmpeg.to_string(),
            logger: logger.clone(),
        }
    }

    pub fn make_encoder(
        &self,
        format: &AudioFormat,
    ) -> Result<
        (
            mpsc::Sender<Result<Bytes, io::Error>>,
            mpsc::Receiver<Result<Bytes, io::Error>>,
        ),
        AudioEncoderError,
    > {
        let (input_sender, input_receiver) = mpsc::channel::<Result<Bytes, io::Error>>(4);
        let (output_sender, output_receiver) = mpsc::channel::<Result<Bytes, io::Error>>(4);

        debug!(self.logger, "Spawning audio encoder process...");

        let process = match Command::new(&self.path_to_ffmpeg)
            .args(&[
                "-acodec",
                "pcm_s16le",
                "-ar",
                "44100",
                "-ac",
                "2",
                "-f",
                "s16le",
                "-i",
                "-",
                "-af",
                "compand=0 0:1 1:-90/-900 -70/-70 -21/-21 0/-15:0.01:12:0:0",
                "-map_metadata",
                "-1",
                "-vn",
                "-ar",
                "44100",
                "-ac",
                "2",
                "-b:a",
                &format!("{}k", format.bitrate()),
                "-codec:a",
                &format.codec(),
                "-f",
                &format.format(),
                "-",
            ])
            .stdin(Stdio::piped())
            .stdout(Stdio::piped())
            .stderr(Stdio::null())
            .spawn()
        {
            Ok(process) => process,
            Err(error) => {
                error!(self.logger, "Unable to start process"; "error" => ?error);
                return Err(AudioEncoderError::ProcessError);
            }
        };

        debug!(self.logger, "Audio encoder spawned");

        let stdout = match process.stdout {
            Some(stdout) => stdout,
            None => {
                error!(self.logger, "Stdout is not available");
                return Err(AudioEncoderError::StdoutUnavailable);
            }
        };

        let stdin = match process.stdin {
            Some(stdin) => stdin,
            None => {
                error!(self.logger, "Stdin is not available");
                return Err(AudioEncoderError::StdinUnavailable);
            }
        };

        let (term_signal, term_handler) = oneshot::channel::<()>();

        // Read raw audio data and send to the encoder
        actix_rt::spawn({
            let logger = self.logger.clone();

            let pipe = async move {
                read_to_stdin(input_receiver, stdin, logger).await;
            };

            let abort = async move {
                let _ = term_handler.await;
            };

            abort.or(pipe)
        });

        // Read encoded audio data from encoder and send to stdout_sender.
        actix_rt::spawn({
            let logger = self.logger.clone();

            async move {
                send_from_stdout(stdout, output_sender, logger).await;
                let _ = term_signal.send(());
            }
        });

        Ok((input_sender, output_receiver))
    }
}
