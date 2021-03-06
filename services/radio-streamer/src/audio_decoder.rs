use actix_web::web::Bytes;
use async_process::{Command, Stdio};
use futures::channel::mpsc;
use futures::{io, AsyncReadExt, SinkExt};
use slog::Logger;

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

    pub fn decode_audio_file(&self, url: &str) -> mpsc::Receiver<Result<Bytes, io::Error>> {
        let (sender, receiver) = mpsc::channel(4);

        let child = Command::new(&self.path_to_ffmpeg)
            .args(&["-i", &url, "-f", "mp3", "-"])
            .stdout(Stdio::piped())
            .spawn()
            .unwrap();

        actix_rt::spawn({
            let mut stdout = child.stdout.unwrap();
            let mut sender = sender;
            let mut input_buffer = vec![0u8; 4096];

            async move {
                loop {
                    match stdout.read(&mut input_buffer).await {
                        Ok(read_bytes) => {
                            if read_bytes == 0 {
                                break;
                            }
                            sender
                                .send(Ok(Bytes::copy_from_slice(&input_buffer[..read_bytes])))
                                .await
                                .unwrap()
                        }
                        Err(_) => {
                            return;
                        }
                    }
                }
            }
        });

        receiver
    }
}
