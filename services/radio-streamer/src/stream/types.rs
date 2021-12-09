use actix_rt::time::Instant;
use actix_web::web::Bytes;
use std::time::Duration;

#[derive(Clone, Debug)]
pub(crate) struct DecodedBuffer(pub(crate) Bytes, pub(crate) Duration);

#[derive(Clone, Debug)]
pub(crate) struct TimedBuffer(pub(crate) Bytes, pub(crate) Instant);
