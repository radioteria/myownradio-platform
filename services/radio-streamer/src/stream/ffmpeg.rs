use crate::audio_formats::AudioFormat;
use crate::helpers::io::{read_exact_from_stdout, read_from_stdout, write_to_stdin};
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
use std::io::ErrorKind;
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

struct PacketInfo {
    pts: Duration,
    dts: Duration,
    size: usize,
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

    let mut start_time = Some(Instant::now());

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

    let (next_packet_sender, next_packet_receiver) = mpsc::channel::<PacketInfo>(10);

    actix_rt::spawn({
        let logger = logger.clone();

        let mut next_packet_sender = next_packet_sender;

        async move {
            let mut err_lines = BufReader::new(stderr).split(b'\n');

            while let Some(Ok(line)) = err_lines.next().await {
                let line = String::from_utf8_lossy(&line);

                trace!(logger, "ffmpeg stderr: {}", line);

                if let Some(captures) = FFMPEG_MUXER_PACKET_REGEX.captures(&line) {
                    let pts = Duration::from_secs_f64(captures[2].parse().unwrap());
                    let dts = Duration::from_secs_f64(captures[4].parse().unwrap());
                    let size = captures[5].parse().unwrap();

                    let _ = next_packet_sender.send(PacketInfo { pts, dts, size }).await;
                }
            }

            drop(err_lines);
        }
    });

    let (mut output_sender, output_receiver) = mpsc::channel::<DecoderOutput>(0);

    actix_rt::spawn({
        let metrics = metrics.clone();
        let logger = logger.clone();

        let mut next_packet_receiver = next_packet_receiver;

        async move {
            metrics.inc_active_decoders();

            defer!(metrics.dec_active_decoders());

            let mut stdout = BufReader::new(stdout);

            let mut channel_closed = false;

            loop {
                let next_packet_info = match next_packet_receiver.next().await {
                    Some(d) => d,
                    None => break,
                };

                trace!(
                    logger,
                    "planned packet size={} pts={:?} dts={:?}",
                    &next_packet_info.size,
                    &next_packet_info.pts,
                    &next_packet_info.dts,
                );

                match read_exact_from_stdout(&mut stdout, &next_packet_info.size).await {
                    Some(Ok(bytes)) => {
                        if let Some(time) = start_time.take() {
                            metrics.update_audio_decoder_track_open_duration(time.elapsed());
                        }

                        if let Err(_) = output_sender
                            .send(DecoderOutput::Buffer(Buffer::new(
                                bytes,
                                next_packet_info.pts,
                            )))
                            .await
                        {
                            channel_closed = true;
                            break;
                        };
                    }
                    _ => break,
                }
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

    let (next_packet_sender, next_packet_receiver) = mpsc::channel::<PacketInfo>(100);

    let buffer = Arc::new(Mutex::new(Vec::<u8>::new()));
    let next_packet_park = Arc::new(Mutex::new(None::<oneshot::Sender<()>>));

    actix_rt::spawn({
        let mut next_packet_receiver = next_packet_receiver;
        let mut output_sender = output_sender.clone();
        let input_close_sender = stdin_term_sender;

        let buffer = Arc::clone(&buffer);
        let next_packet_park = Arc::clone(&next_packet_park);

        async move {
            while let Some(packet_info) = next_packet_receiver.next().await {
                eprintln!(
                    "Packet size {} bytes, buffer contains {} bytes",
                    packet_info.size,
                    buffer.lock().unwrap().len()
                );

                loop {
                    let mut buffer_guard = buffer.lock().unwrap();

                    if buffer_guard.len() >= packet_info.size {
                        let packet_bytes = Bytes::copy_from_slice(
                            buffer_guard.drain(..packet_info.size).as_slice(),
                        );
                        drop(buffer_guard);

                        let encoded_buffer = Buffer::new(packet_bytes, packet_info.dts);
                        if let Err(_) = output_sender
                            .send(EncoderOutput::Buffer(encoded_buffer))
                            .await
                        {
                            let _ = input_close_sender.send(());
                            return;
                        };

                        break;
                    } else {
                        let (sender, receiver) = oneshot::channel::<()>();
                        next_packet_park.lock().unwrap().replace(sender);
                        drop(buffer_guard);

                        if receiver.await.is_err() {
                            let _ = input_close_sender.send(());
                            return;
                        }
                    }
                }
            }
        }
    });

    actix_rt::spawn({
        let logger = logger.clone();

        let mut next_packet_sender = next_packet_sender.clone();

        async move {
            let mut err_lines = BufReader::new(stderr).split(b'\n');

            while let Some(Ok(line)) = err_lines.next().await {
                let line = String::from_utf8_lossy(&line);

                trace!(logger, "ffmpeg stderr: {}", line);

                if let Some(captures) = FFMPEG_MUXER_PACKET_REGEX.captures(&line) {
                    let pts = Duration::from_secs_f64(captures[2].parse().unwrap());
                    let dts = Duration::from_secs_f64(captures[4].parse().unwrap());
                    let size = captures[5].parse().unwrap();

                    let packet_info = PacketInfo { pts, dts, size };

                    if next_packet_sender.send(packet_info).await.is_err() {
                        break;
                    }
                }
            }

            drop(err_lines);
        }
    });

    actix_rt::spawn({
        let mut stdout = BufReader::with_capacity(32767, stdout);
        let mut output_sender = output_sender.clone();
        let buffer = Arc::clone(&buffer);
        let next_packet_park = Arc::clone(&next_packet_park);

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

                buffer
                    .lock()
                    .unwrap()
                    .extend_from_slice(&stdout_read_buffer[..size]);

                if let Some(sender) = next_packet_park.lock().unwrap().take() {
                    let _ = sender.send(());
                }
            }

            drop(stdout);

            let _ = output_sender.send(EncoderOutput::EOF).await;
        }
    });

    Ok((input_sender, output_receiver))
}
