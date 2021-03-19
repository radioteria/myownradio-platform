use actix_web::web::Bytes;
use futures::channel::mpsc::Receiver as AsyncReceiver;
use std::collections::HashMap;
use std::io;
use std::sync::mpsc::Receiver as SyncReceiver;
use std::sync::{Arc, Mutex};

#[derive(Hash)]
pub(crate) struct DecoderKey(usize);

pub(crate) struct DecoderEntry(
    AsyncReceiver<Result<Bytes, io::Error>>,
    SyncReceiver<String>,
);

pub(crate) struct DecoderRegistry {
    decoders: Arc<Mutex<HashMap<DecoderKey, DecoderEntry>>>,
}

impl DecoderRegistry {
    pub async fn get_or_create_decoder(&self, channel_id: usize) -> DecoderEntry {
        unimplemented!()
    }

    pub async fn create_decoder(&self, channel_id: usize) -> DecoderEntry {
        unimplemented!()
    }
}
