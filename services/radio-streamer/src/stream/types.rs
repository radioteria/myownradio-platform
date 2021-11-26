use actix_web::web::Bytes;
use std::time::Duration;

pub(crate) struct TimedBuffer(pub(crate) Bytes, pub(crate) Duration);
