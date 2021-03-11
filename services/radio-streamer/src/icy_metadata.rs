use actix_web::web::Bytes;
use bytebuffer::ByteBuffer;
use std::sync;

const ICY_META_SIZE_MULTIPLIER: usize = 16;

pub struct IcyMetadataMuxer {
    interval: usize,
    bytes_remaining: usize,
    title_receiver: sync::mpsc::Receiver<String>,
}

impl IcyMetadataMuxer {
    pub fn new(interval: usize, title_receiver: sync::mpsc::Receiver<String>) -> Self {
        let bytes_remaining = interval;

        IcyMetadataMuxer {
            interval,
            bytes_remaining,
            title_receiver,
        }
    }

    pub fn handle_source_bytes(&mut self, bytes: Bytes) -> Bytes {
        let bytes_len = bytes.len();

        if self.bytes_remaining > bytes_len {
            self.bytes_remaining -= bytes_len;
            return bytes;
        }

        let slices = [
            bytes.slice(0..self.bytes_remaining),
            Bytes::from(self.make_metadata_chunk()),
            bytes.slice(self.bytes_remaining..),
        ];

        self.bytes_remaining = self.interval - (bytes_len - self.bytes_remaining);

        Bytes::from(slices.concat())
    }

    fn make_metadata_chunk(&self) -> Vec<u8> {
        let mut buffer = ByteBuffer::new();

        match self.title_receiver.try_recv() {
            Ok(title) => {
                let metadata = format!("StreamTitle='{}';", title);
                let metadata_len = metadata.len();
                let size_byte =
                    (metadata_len as f32 / ICY_META_SIZE_MULTIPLIER as f32).ceil() as usize;

                let mut text_buffer = ByteBuffer::from_bytes(metadata.as_bytes());
                text_buffer.resize(size_byte * ICY_META_SIZE_MULTIPLIER);

                buffer.write_u8(size_byte as u8);
                buffer.write_bytes(&text_buffer.to_bytes());
            }
            _ => {
                buffer.write_u8(0u8);
            }
        }

        buffer.to_bytes()
    }
}
