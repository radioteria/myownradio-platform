use crate::helpers::io::read_from_stdout;
use crate::metrics::Metrics;
use crate::stream::constants::{
    AUDIO_BYTES_PER_SECOND, AUDIO_CHANNELS_NUMBER, AUDIO_SAMPLING_FREQUENCY,
};
use crate::stream::types::DecodedBuffer;
use async_process::{Command, Stdio};
use futures::channel::mpsc;
use futures::SinkExt;
use scopeguard::defer;
use slog::{debug, error, o, warn, Logger};
use std::time::Duration;

const STDIO_BUFFER_SIZE: usize = 4096;

#[derive(Debug)]
pub(crate) enum DecoderError {
    ProcessError,
    StdoutUnavailable,
}

pub(crate) fn make_ffmpeg_decoder(
    source_url: &str,
    offset: &Duration,
    path_to_ffmpeg: &str,
    logger: &Logger,
    metrics: &Metrics,
) -> Result<mpsc::Receiver<DecodedBuffer>, DecoderError> {
    let (mut tx, rx) = mpsc::channel::<DecodedBuffer>(0);
    let logger = logger.new(o!("kind" => "ffmpeg_decoder"));

    let mut process = match Command::new(&path_to_ffmpeg)
        .args(&[
            "-v",
            "quiet",
            "-hide_banner",
            "-ss",
            &format!("{:.4}", offset.as_secs_f32()),
            "-i",
            &source_url,
            "-vn",
            "-codec:a",
            "pcm_s16le",
            "-ar",
            &AUDIO_SAMPLING_FREQUENCY.to_string(),
            "-ac",
            &AUDIO_CHANNELS_NUMBER.to_string(),
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
            error!(logger, "Unable to start the decoder process"; "error" => ?error);
            return Err(DecoderError::ProcessError);
        }
    };

    debug!(logger, "Starting audio decoder"; "url" => source_url, "offset" => ?offset);

    let status = process.status();

    let stdout = match process.stdout {
        Some(stdout) => stdout,
        None => {
            error!(logger, "Unable to start decoder: stdout is not available");
            return Err(DecoderError::StdoutUnavailable);
        }
    };

    actix_rt::spawn({
        let metrics = metrics.clone();

        let mut bytes_sent = 0usize;

        async move {
            metrics.inc_active_decoders();

            defer!(metrics.dec_active_decoders());

            let mut stdout = stdout;
            let mut buffer = vec![0u8; STDIO_BUFFER_SIZE];

            while let Some(Ok(bytes)) = read_from_stdout(&mut stdout, &mut buffer).await {
                let bytes_len = bytes.len();
                let decoding_time_seconds = bytes_sent as f64 / AUDIO_BYTES_PER_SECOND as f64;
                let decoding_time = Duration::from_secs_f64(decoding_time_seconds);
                let timed_bytes = DecodedBuffer(bytes, decoding_time);

                if let Err(_) = tx.send(timed_bytes).await {
                    break;
                };

                bytes_sent += bytes_len;
            }

            drop(stdout);

            if let Ok(exit_status) = status.await {
                match exit_status.code() {
                    Some(code) if code != 0 => {
                        warn!(logger, "Decoder existed with non-zero exit code"; "exit_code" => code);
                    }
                    _ => (),
                }
            }
        }
    });

    Ok(rx)
}
