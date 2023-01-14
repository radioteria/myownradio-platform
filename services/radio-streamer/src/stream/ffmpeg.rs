use crate::audio_formats::AudioFormat;
use crate::helpers::io::{read_from_stdout, write_to_stdin};
use crate::helpers::system::which;
use crate::metrics::Metrics;
use crate::stream::constants::{AUDIO_CHANNELS_NUMBER, AUDIO_SAMPLING_FREQUENCY};
use crate::stream::types::Buffer;
use actix_web::web::Bytes;
use async_process::{Command, Stdio};
use futures::channel::{mpsc, oneshot};
use futures::io::BufReader;
use futures::{SinkExt, StreamExt};
use futures_lite::{AsyncBufReadExt, FutureExt};
use lazy_static::lazy_static;
use regex::Regex;
use scopeguard::defer;
use slog::{error, info, o, trace, warn, Logger};
use std::sync::{Arc, Mutex};
use std::time::{Duration, Instant};

const STDOUT_READ_BUFFER_SIZE: usize = 4096;

lazy_static! {
    static ref FFMPEG_COMMAND: &'static str =
        Box::leak(Box::new(which("ffmpeg").expect("Unable to locate ffmpeg")));
    static ref FFPROBE_COMMAND: &'static str = Box::leak(Box::new(
        which("ffprobe").expect("Unable to locate ffprobe")
    ));
    // muxer <- type:audio pkt_pts:288639 pkt_pts_time:6.01331 pkt_dts:288639 pkt_dts_time:6.01331 size:17836
    static ref FFMPEG_MUXER_PACKET_REGEX: &'static Regex =
        Box::leak(Box::new(Regex::new(r"muxer <- type:audio pkt_pts:([0-9]+) pkt_pts_time:([0-9]+\.[0-9]+) pkt_dts:([0-9]+) pkt_dts_time:([0-9]+\.[0-9]+) size:([0-9]+)").unwrap()));
}

#[derive(Clone, Debug, Default)]
struct PacketInfo {
    pts_hint: Duration,
    dts_hint: Duration,
}

#[derive(Debug)]
pub(crate) enum DecoderError {
    ProcessError,
    StdoutUnavailable,
    StderrUnavailable,
}

pub(crate) enum DecoderOutput {
    Buffer(Buffer),
    EOF,
    Error(i32),
}

pub(crate) fn build_ffmpeg_decoder(
    source_url: &str,
    offset: &Duration,
    logger: &Logger,
    metrics: &Metrics,
) -> Result<mpsc::Receiver<DecoderOutput>, DecoderError> {
    let logger = logger.new(o!("kind" => "ffmpeg_decoder"));

    let mut process = match Command::new(*FFMPEG_COMMAND)
        .args(&[
            "-debug_ts",
            "-v",
            "info",
            "-nostats",
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
        .stderr(Stdio::piped())
        .stdin(Stdio::piped())
        .spawn()
    {
        Ok(process) => process,
        Err(error) => {
            error!(logger, "Unable to start the decoder process"; "error" => ?error);
            return Err(DecoderError::ProcessError);
        }
    };

    info!(logger, "Started audio decoder"; "url" => source_url, "offset" => ?offset);

    let status = process.status();

    let stdout = match process.stdout.take() {
        Some(stdout) => stdout,
        None => {
            error!(logger, "Unable to start decoder: stdout is not available");
            return Err(DecoderError::StdoutUnavailable);
        }
    };

    let stderr = match process.stderr.take() {
        Some(stderr) => stderr,
        None => {
            error!(logger, "Unable to start decoder: stderr is not available");
            return Err(DecoderError::StderrUnavailable);
        }
    };

    let last_packet_info = Arc::new(Mutex::new(None::<PacketInfo>));

    actix_rt::spawn({
        let logger = logger.clone();

        let last_packet_info = last_packet_info.clone();

        async move {
            let mut err_lines = BufReader::new(stderr).split(b'\n');

            while let Some(Ok(line)) = err_lines.next().await {
                let line = String::from_utf8_lossy(&line);

                trace!(logger, "ffmpeg stderr: {}", line);

                if let Some(captures) = FFMPEG_MUXER_PACKET_REGEX.captures(&line) {
                    let pts_hint = Duration::from_secs_f64(captures[2].parse().unwrap());
                    let dts_hint = Duration::from_secs_f64(captures[4].parse().unwrap());

                    let packet_info = PacketInfo { pts_hint, dts_hint };

                    last_packet_info.lock().unwrap().replace(packet_info);
                }
            }

            drop(err_lines);
        }
    });

    let (mut output_sender, output_receiver) = mpsc::channel::<DecoderOutput>(0);

    actix_rt::spawn({
        let metrics = metrics.clone();
        let logger = logger.clone();

        let last_packet_info = last_packet_info.clone();

        async move {
            metrics.inc_active_decoders();

            defer!(metrics.dec_active_decoders());

            let mut stdout = BufReader::new(stdout);

            let mut channel_closed = false;

            let mut stdout_read_buffer = vec![0u8; STDOUT_READ_BUFFER_SIZE];
            while let Ok(size) = read_from_stdout(&mut stdout, &mut stdout_read_buffer).await {
                let next_packet_info = last_packet_info.lock().unwrap().clone().unwrap_or_default();
                let buffer_bytes = Bytes::copy_from_slice(&stdout_read_buffer[..size]);

                if let Err(_) = output_sender
                    .send(DecoderOutput::Buffer(Buffer::new(
                        buffer_bytes,
                        next_packet_info.dts_hint,
                    )))
                    .await
                {
                    channel_closed = true;
                    break;
                };
            }

            let _ = output_sender.send(DecoderOutput::EOF).await;

            drop(stdout);

            if let Ok(exit_status) = status.await {
                match exit_status.code() {
                    Some(code) if code == 1 && channel_closed => {
                        trace!(
                            logger,
                            "Decoder exited because output channel has been closed"
                        );
                    }
                    Some(code) if code != 0 => {
                        warn!(logger, "Decoder exited with non-zero exit code"; "exit_code" => code);

                        let _ = output_sender.send(DecoderOutput::Error(code)).await;
                    }
                    _ => (),
                }
            }
        }
    });

    Ok(output_receiver)
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum EncoderError {
    #[error("Error while processing data")]
    ProcessError,
    #[error("Unable to access stdout")]
    StdoutUnavailable,
    #[error("Unable to access stdin")]
    StdinUnavailable,
    #[error("Unable to access stderr")]
    StderrUnavailable,
}

pub(crate) enum EncoderOutput {
    Buffer(Buffer),
    EOF,
}

pub(crate) fn build_ffmpeg_encoder(
    audio_format: &AudioFormat,
    logger: &Logger,
    metrics: &Metrics,
) -> Result<(mpsc::Sender<Buffer>, mpsc::Receiver<EncoderOutput>), EncoderError> {
    let logger = logger.new(o!("kind" => "ffmpeg_encoder"));

    let mut process = match Command::new(*FFMPEG_COMMAND)
        .args(&[
            "-debug_ts",
            "-v",
            "info",
            "-nostats",
            "-hide_banner",
            "-acodec",
            "pcm_s16le",
            "-ar",
            &AUDIO_SAMPLING_FREQUENCY.to_string(),
            "-ac",
            &AUDIO_CHANNELS_NUMBER.to_string(),
            "-f",
            "s16le",
            "-i",
            "-",
            // TODO Replace with apply of pre-computed audio peak level.
            // "-af",
            // "compand=0 0:1 1:-90/-900 -70/-70 -21/-21 0/-15:0.01:12:0:0",
            "-map_metadata",
            "-1",
            "-vn",
            "-ar",
            &AUDIO_SAMPLING_FREQUENCY.to_string(),
            "-ac",
            "2",
            "-b:a",
            &format!("{}k", audio_format.bitrate),
            "-codec:a",
            &audio_format.codec,
            "-f",
            &audio_format.format,
            "-",
        ])
        .stdin(Stdio::piped())
        .stdout(Stdio::piped())
        .stderr(Stdio::piped())
        .spawn()
    {
        Ok(process) => process,
        Err(error) => {
            error!(logger, "Unable to start encoder process: error occurred"; "error" => ?error);
            return Err(EncoderError::ProcessError);
        }
    };

    let stdout = process
        .stdout
        .take()
        .ok_or_else(|| EncoderError::StdoutUnavailable)?;

    let stdin = process
        .stdin
        .take()
        .ok_or_else(|| EncoderError::StdinUnavailable)?;

    let stderr = process
        .stderr
        .take()
        .ok_or_else(|| EncoderError::StderrUnavailable)?;

    let (input_sender, mut input_receiver) = mpsc::channel::<Buffer>(0);
    let (output_sender, output_receiver) = mpsc::channel::<EncoderOutput>(0);

    let (stdin_term_sender, stdin_term_receiver) = oneshot::channel::<()>();
    actix_rt::spawn({
        let mut stdin = stdin;

        let logger = logger.clone();

        let pipe = async move {
            while let Some(buffer) = input_receiver.next().await {
                if let Err(error) = write_to_stdin(&mut stdin, buffer.into_bytes()).await {
                    error!(logger, "Unable to write data to encoder: error occurred"; "error" => ?error);
                    break;
                }
            }

            drop(stdin);
        };

        let term = async move {
            let _ = stdin_term_receiver.await;
        };

        pipe.or(term)
    });

    let last_packet_info = Arc::new(Mutex::new(None::<PacketInfo>));

    actix_rt::spawn({
        let logger = logger.clone();
        let last_packet_info = last_packet_info.clone();

        async move {
            let mut err_lines = BufReader::new(stderr).split(b'\n');

            while let Some(Ok(line)) = err_lines.next().await {
                let line = String::from_utf8_lossy(&line);

                trace!(logger, "ffmpeg stderr: {}", line);

                if let Some(captures) = FFMPEG_MUXER_PACKET_REGEX.captures(&line) {
                    let pts_hint = Duration::from_secs_f64(captures[2].parse().unwrap());
                    let dts_hint = Duration::from_secs_f64(captures[4].parse().unwrap());

                    let packet_info = PacketInfo { pts_hint, dts_hint };

                    last_packet_info.lock().unwrap().replace(packet_info);
                }
            }

            drop(err_lines);
        }
    });

    actix_rt::spawn({
        let mut stdout = BufReader::with_capacity(32767, stdout);
        let mut output_sender = output_sender.clone();

        let stdin_term_sender = stdin_term_sender;
        let last_packet_info = last_packet_info.clone();

        let metrics = metrics.clone();
        let format_string = audio_format.to_string();

        async move {
            metrics.inc_active_encoders(&format_string);
            defer!(metrics.dec_active_encoders(&format_string));

            let mut stdout_read_buffer = vec![0u8; STDOUT_READ_BUFFER_SIZE];
            while let Ok(size) = read_from_stdout(&mut stdout, &mut stdout_read_buffer).await {
                if size == 0 {
                    break;
                }

                let packet_info = last_packet_info.lock().unwrap().clone().unwrap_or_default();

                let buffer_bytes = Bytes::copy_from_slice(&stdout_read_buffer[..size]);
                let encoded_buffer = Buffer::new(buffer_bytes, packet_info.dts_hint);
                let msg = EncoderOutput::Buffer(encoded_buffer);

                if let Err(_) = output_sender.send(msg).await {
                    let _ = stdin_term_sender.send(());
                    return;
                };
            }

            drop(stdout);

            let _ = output_sender.send(EncoderOutput::EOF).await;
        }
    });

    Ok((input_sender, output_receiver))
}
