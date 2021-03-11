use crate::helpers::math::div_ceil;
use actix_web::web::Bytes;
use bytebuffer::ByteBuffer;
use std::sync;

const ICY_META_SIZE_MULTIPLIER: usize = 16;

pub struct IcyMetadataMuxer {
    interval: usize,
    bytes_remaining: usize,
    metadata_receiver: sync::mpsc::Receiver<Vec<u8>>,
}

impl IcyMetadataMuxer {
    pub fn new(interval: usize, metadata_receiver: sync::mpsc::Receiver<Vec<u8>>) -> Self {
        let bytes_remaining = interval;

        IcyMetadataMuxer {
            interval,
            bytes_remaining,
            metadata_receiver,
        }
    }

    pub fn handle_bytes(&mut self, bytes: Bytes) -> Bytes {
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

        match self.metadata_receiver.try_recv() {
            Ok(metadata) => {
                let size_byte_value = div_ceil(metadata.len(), ICY_META_SIZE_MULTIPLIER);

                buffer.resize(1 + size_byte_value * ICY_META_SIZE_MULTIPLIER);
                buffer.write_u8(size_byte_value as u8);
                buffer.write_bytes(&metadata);
            }
            _ => {
                buffer.write_u8(0u8);
            }
        }

        buffer.to_bytes()
    }
}
