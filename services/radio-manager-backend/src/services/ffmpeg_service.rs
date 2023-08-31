use async_process::Command;
use bytes::Bytes;
use futures::channel::mpsc::Sender;
use futures::{AsyncReadExt, SinkExt};
use std::process::Stdio;
use std::time::Duration;

#[derive(thiserror::Error, Debug)]
pub(crate) enum TranscodeAudioFileError {
    #[error("Failed to spawn ffmpeg process: {0}")]
    IO(#[from] std::io::Error),
    #[error("Failed to open stdout")]
    NoStdout,
}

pub(crate) enum AudioCodec {
    Aac,
}

pub(crate) enum AudioContainer {
    Adts,
}

pub(crate) enum AudioChannels {
    Mono,
    Stereo,
}

pub(crate) struct TranscodeAudioFileFormat {
    pub(crate) codec: AudioCodec,
    pub(crate) container: AudioContainer,
    pub(crate) channels: AudioChannels,
    pub(crate) bitrate: u32,
    pub(crate) sampling_rate: u32,
}

pub(crate) async fn transcode_audio_file(
    input_file: &str,
    output_tx: Sender<Bytes>,
    initial_position: Duration,
    format: TranscodeAudioFileFormat,
) -> Result<(), TranscodeAudioFileError> {
    let mut process = Command::new("ffmpeg")
        .arg("-ss")
        .arg(format!("{:.3}", initial_position.as_secs_f64()))
        .arg("-i")
        .arg(input_file)
        .arg("-acodec")
        .arg(match format.codec {
            AudioCodec::Aac => "aac",
        })
        .arg("-b:a")
        .arg(format!("{}", format.bitrate))
        .arg("-ar")
        .arg(format!("{}", format.sampling_rate))
        .arg("-ac")
        .arg(format!(
            "{}",
            match format.channels {
                AudioChannels::Mono => 1,
                AudioChannels::Stereo => 2,
            }
        ))
        .arg("-f")
        .arg(match format.container {
            AudioContainer::Adts => "adts",
        })
        .arg("pipe:1")
        .stdout(Stdio::piped())
        .spawn()?;

    let mut stdout = process
        .stdout
        .take()
        .ok_or_else(|| TranscodeAudioFileError::NoStdout)?;

    let mut buffer = [0u8; 4096];
    let mut output_tx = output_tx;

    loop {
        let bytes_read = stdout.read(&mut buffer).await?;

        if bytes_read == 0 {
            break; // EOF reached
        }

        let chunk = &buffer[0..bytes_read];

        if output_tx.send(Bytes::copy_from_slice(chunk)).await.is_err() {
            break;
        }
    }

    Ok(())
}
