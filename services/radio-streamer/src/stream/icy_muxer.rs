use actix_web::web::Bytes;
use bytebuffer::ByteBuffer;
use std::sync::Mutex;

fn div_ceil(a: usize, b: usize) -> usize {
    (a as f32 / b as f32).ceil() as usize
}

pub const ICY_METADATA_INTERVAL: usize = 8192;

const METADATA_SIZE_MULTIPLIER: usize = 16;

pub struct IcyMuxer {
    bytes_remaining: Mutex<usize>,
    track_title_to_send: Mutex<Option<String>>,
}

impl IcyMuxer {
    pub fn new() -> Self {
        let bytes_remaining = Mutex::new(ICY_METADATA_INTERVAL);
        let track_title_to_send = Mutex::default();

        IcyMuxer {
            bytes_remaining,
            track_title_to_send,
        }
    }

    pub fn send_track_title(&self, title: String) {
        self.track_title_to_send.lock().unwrap().replace(title);
    }

    pub fn handle_bytes(&self, bytes: Bytes) -> Bytes {
        let bytes_len = bytes.len();

        let mut bytes_remaining = self.bytes_remaining.lock().unwrap();

        if *bytes_remaining > bytes_len {
            *bytes_remaining -= bytes_len;
            return bytes;
        }

        let mut buffer = ByteBuffer::new();

        buffer.write_bytes(&bytes[0..*bytes_remaining]);
        buffer.write_bytes(&self.make_metadata_bytes());
        buffer.write_bytes(&bytes[*bytes_remaining..]);

        *bytes_remaining = ICY_METADATA_INTERVAL - (bytes_len - *bytes_remaining);

        Bytes::from(buffer.to_bytes())
    }

    fn make_metadata_bytes(&self) -> Vec<u8> {
        let mut buffer = ByteBuffer::new();

        match self.track_title_to_send.lock().unwrap().take() {
            Some(title) => {
                let metadata = format!("StreamTitle='{}';", &title).into_bytes();
                let size_byte_value = div_ceil(metadata.len(), METADATA_SIZE_MULTIPLIER);

                buffer.resize(1 + size_byte_value * METADATA_SIZE_MULTIPLIER);
                buffer.write_u8(size_byte_value as u8);
                buffer.write_bytes(&metadata);
            }
            None => {
                buffer.write_u8(0u8);
            }
        }

        buffer.to_bytes()
    }
}
