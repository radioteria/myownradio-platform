use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};

use actix_web::{App, HttpServer};
use slog::{info, o, Drain, Logger};
use slog_json::Json;

use crate::codec::AudioCodecService;
use crate::config::Config;
use crate::http::metrics::get_metrics;
use crate::http::streaming::listen_by_channel_id;
use crate::metrics::Metrics;
use crate::mor_backend_client::MorBackendClient;

mod audio_formats;
mod codec;
mod config;
mod helpers;
mod http;
mod icy_metadata;
mod metrics;
mod mor_backend_client;

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

    let mor_backend_client = Arc::new(MorBackendClient::new(
        &config.mor_backend_url,
        &logger.new(o!("scope" => "MorBackendClient")),
    ));
    let audio_codec_service = Arc::new(AudioCodecService::new(
        &config.path_to_ffmpeg,
        &logger.new(o!("scope" => "AudioCodecService")),
    ));
    let metrics = Arc::new(Metrics::new());

    info!(logger, "Starting application...");

    let server = HttpServer::new({
        let logger = logger.clone();
        move || {
            App::new()
                .app_data(config.clone())
                .app_data(mor_backend_client.clone())
                .app_data(logger.clone())
                .app_data(metrics.clone())
                .app_data(audio_codec_service.clone())
                .service(listen_by_channel_id)
                .service(get_metrics)
        }
    })
    .bind(bind_address)?;

    info!(logger, "Application started");

    server.run().await
}
