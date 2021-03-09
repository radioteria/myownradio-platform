use actix_web::web::Bytes;
use bytebuffer::ByteBuffer;
use futures::channel::mpsc::{SendError, Sender};
use futures::{io, SinkExt};
use std::io::Write;

pub struct IcyMetadata {
    icy_interval: usize,
    remaining: usize,
    title: Option<String>,
    target: Sender<Result<Bytes, io::Error>>,
}

impl IcyMetadata {
    pub fn new(icy_interval: usize, target: Sender<Result<Bytes, io::Error>>) -> Self {
        let remaining = icy_interval;
        let title = None;

        IcyMetadata {
            icy_interval,
            title,
            remaining,
            target,
        }
    }

    pub async fn send(&mut self, item: Result<Bytes, io::Error>) -> Result<(), SendError> {
        match item {
            Ok(bytes) => {
                if self.remaining > bytes.len() {
                    self.remaining -= bytes.len();
                    return self.target.send(Ok(bytes)).await;
                }

                self.target.send(Ok(bytes.slice(0..self.remaining))).await?;
                self.target.send(Ok(self.get_metadata_chunk()[..])).await?;
                self.target.send(Ok(bytes.slice(self.remaining..))).await?;

                self.remaining = self.icy_interval - (bytes.len() - self.remaining);

                Ok(())
            }
            Err(error) => self.target.send(Err(error)).await,
        }
    }

    pub fn update_title(&mut self, new_title: &str) {
        self.title = Some(new_title.to_string())
    }

    fn get_metadata_chunk(&mut self) -> ByteBuffer {
        let mut buffer = ByteBuffer::new();
        match &self.title {
            Some(title) => {
                let actual_title_size = title.len();
                let size = title.len() as u8 / 16;
                let alloc_title_size = size * 16;
                let size_byte_value = 1 + size;
                buffer.write_u8(size_byte_value);
                buffer.write(title.as_bytes());
                buffer.write(&[0, alloc_title_size - actual_title_size]);
                self.title = None;
            }
            None => buffer.write_u8(0),
        }
        buffer
    }
}
