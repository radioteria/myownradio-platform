use actix_web::dev::Service;
use actix_web::{App, HttpServer};
use slog::{info, o, Drain, Logger};
use slog_json::Json;
use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};
use std::time::Instant;

use crate::codec::AudioCodecService;
use crate::config::Config;
use crate::http::metrics::get_metrics;
use crate::http::streaming::{get_active_streams, listen_by_channel_id, restart_by_channel_id};
use crate::metrics::Metrics;
use crate::mor_backend_client::MorBackendClient;
use crate::restart_registry::RestartRegistry;

mod audio_formats;
mod codec;
mod config;
mod helpers;
mod http;
mod icy_metadata;
mod metrics;
mod mor_backend_client;
mod restart_registry;
mod stream;

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
    let restart_registry = Arc::new(RestartRegistry::new(
        logger.new(o!("scope" => "RestartRegistry")),
    ));

    info!(logger, "Starting application...");

    let server = HttpServer::new({
        let logger = logger.clone();
        move || {
            App::new()
                .wrap_fn({
                    let metrics = metrics.clone();

                    move |req, srv| {
                        let instant = Instant::now();
                        let fut = srv.call(req);
                        let metrics = metrics.clone();

                        async move {
                            let result = fut.await?;

                            let status = result.response().status();
                            let method = result.request().method().clone();
                            let path = result
                                .request()
                                .match_pattern()
                                .unwrap_or_else(|| result.request().path().to_string());

                            if path != "/metrics" {
                                metrics.update_http_request_total(
                                    &path,
                                    &method,
                                    status,
                                    instant.elapsed(),
                                );
                            }

                            Ok(result)
                        }
                    }
                })
                .data(config.clone())
                .data(mor_backend_client.clone())
                .data(logger.clone())
                .data(metrics.clone())
                .data(audio_codec_service.clone())
                .data(restart_registry.clone())
                .service(listen_by_channel_id)
                .service(restart_by_channel_id)
                .service(get_active_streams)
                .service(get_metrics)
        }
    })
    .bind(bind_address)?;

    info!(logger, "Application started");

    server.run().await
}
