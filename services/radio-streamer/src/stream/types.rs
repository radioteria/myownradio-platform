use actix_web::web::Bytes;
use std::time::Duration;

pub(crate) struct TimedBytes(pub(crate) Bytes, pub(crate) Duration);
