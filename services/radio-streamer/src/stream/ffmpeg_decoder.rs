use crate::helpers::io::read_from_stdout;
use crate::metrics::Metrics;
use crate::stream::types::TimedBytes;
use async_process::{Command, Stdio};
use futures::channel::mpsc;
use futures::SinkExt;
use slog::{error, trace, Logger};
use std::time::Duration;

const STDIO_BUFFER_SIZE: usize = 4096;

const SAMPLING_FREQUENCY: usize = 48_000;
const BYTES_PER_SAMPLE: usize = 2;
const AUDIO_CHANNELS: usize = 2;
const BYTES_PER_SECOND: usize = SAMPLING_FREQUENCY * BYTES_PER_SAMPLE * AUDIO_CHANNELS;

#[derive(Debug)]
pub(crate) enum TranscoderError {
    ProcessError,
    StdoutUnavailable,
    StdinUnavailable,
}

pub(crate) fn make_ffmpeg_decoder(
    source_url: &str,
    offset: &Duration,
    path_to_ffmpeg: &str,
    logger: &Logger,
    metrics: &Metrics,
) -> Result<mpsc::Receiver<TimedBytes>, TranscoderError> {
    let (mut tx, rx) = mpsc::channel::<TimedBytes>(0);

    let mut process = match Command::new(&path_to_ffmpeg)
        .args(&[
            "-v",
            "quiet",
            "-hide_banner",
            "-ss",
            &format!("{:.4}", offset.as_secs()),
            "-i",
            &source_url,
            "-vn",
            "-codec:a",
            "pcm_s16le",
            "-ar",
            SAMPLING_FREQUENCY.to_string(),
            "-ac",
            AUDIO_CHANNELS.to_string(),
            "-f",
            "s16le", // BYTES_PER_SAMPLE = 2
            "-",
        ])
        .stdout(Stdio::piped())
        .stderr(Stdio::null())
        .stdin(Stdio::null())
        .spawn()
    {
        Ok(process) => process,
        Err(error) => {
            error!(self.logger, "Unable to spawn the decoder process"; "error" => ?error);
            return Err(TranscoderError::ProcessError);
        }
    };

    let status = process.status();

    let stdout = match process.stdout {
        Some(stdout) => stdout,
        None => {
            error!(logger, "Unable to start decoder: stdout is not available");
            return Err(TranscoderError::StdoutUnavailable);
        }
    };

    actix_rt::spawn({
        let stdout = stdout;
        let logger = logger.clone();
        let metrics = metrics.clone();

        let mut bytes_sent = 0usize;

        async move {
            metrics.inc_spawned_decoder_processes();

            {
                let mut stdout = stdout;
                let mut buffer = vec![0u8; STDIO_BUFFER_SIZE];

                while let Some(Ok(bytes)) = read_from_stdout(&mut stdout, &mut buffer).await {
                    let decoding_time_seconds = (bytes_sent as f64 / BYTES_PER_SECOND as f64);
                    let decoding_time = Duration::from_secs_f64(decoding_time_seconds);
                    let timed_bytes = TimedBytes(bytes, decoding_time);

                    if let Err(error) = tx.send(timed_bytes).await {
                        error!(logger, "Unable to send data from decoder"; "error" => ?error);
                        break;
                    };

                    bytes_sent += bytes.len();
                }
            }

            metrics.dec_spawned_decoder_processes();

            if let Ok(exit_status) = status.await {
                debug!(logger, "Decoder process exited"; "exit_code" => exit_status.code());
            }
        }
    });

    Ok(rx)
}
