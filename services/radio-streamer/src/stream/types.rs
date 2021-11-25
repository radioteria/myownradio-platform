use std::time::Duration;
use actix_web::web::Bytes;

pub(crate) struct TimedBytes(Bytes, Duration);
