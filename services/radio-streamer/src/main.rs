use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};

use actix_web::{App, HttpServer};
use slog::{info, o, Drain, Logger};
use slog_json::Json;

use crate::audio_decoder::AudioDecoder;
use crate::audio_encoder::AudioEncoder;
use crate::config::Config;
use crate::http::listen::listen_by_channel_id;
use crate::mor_backend_client::MorBackendClient;

mod audio_decoder;
mod audio_encoder;
mod config;
mod http;
mod mor_backend_client;
mod utils;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    let config = Arc::new(Config::from_env());
    let bind_address = &config.bind_address.clone();

    let drain = Json::new(io::stderr())
        .add_default_keys()
        .set_pretty(cfg!(debug_assertions))
        .build()
        .filter_level(config.log_level);
    let safe_drain = Mutex::new(drain).map(slog::Fuse);
    let logger = Arc::new(Logger::root(safe_drain, o!("version" => VERSION)));

    let mor_backend_client = Arc::new(MorBackendClient::new(&config.mor_backend_url, &logger));
    let audio_decoder = Arc::new(AudioDecoder::new(
        &config.path_to_ffmpeg,
        &config.path_to_ffprobe,
        &logger,
    ));
    let audio_encoder = Arc::new(AudioEncoder::new(
        &config.path_to_ffmpeg,
        &config.path_to_ffprobe,
        &logger,
    ));

    info!(logger, "Starting application...");

    let server = HttpServer::new({
        let logger = logger.clone();
        move || {
            App::new()
                .data(Arc::clone(&config))
                .data(Arc::clone(&mor_backend_client))
                .data(Arc::clone(&audio_decoder))
                .data(Arc::clone(&audio_encoder))
                .data(Arc::clone(&logger))
                .service(listen_by_channel_id)
        }
    })
    .bind(bind_address)?;

    info!(logger, "Application started");

    server.run().await
}
