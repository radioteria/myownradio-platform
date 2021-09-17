mod audio_formats;
mod backend_client;
mod channel;
mod config;
mod constants;
mod helpers;
mod http;
mod icy_metadata;
mod metrics;
mod transcoder;
mod utils;

use crate::backend_client::BackendClient;
use crate::channel::factory::ChannelPlayerFactory;
use crate::channel::registry::ChannelPlayerRegistry;
use crate::config::Config;
use crate::http::metrics::get_metrics;
use crate::http::streaming::{get_active_streams, listen_by_channel_id, restart_by_channel_id};
use crate::metrics::Metrics;
use crate::transcoder::TranscoderService;
use actix_rt::signal::unix;
use actix_web::dev::Service;
use actix_web::{App, HttpServer};
use futures_lite::FutureExt;
use slog::{info, o, Drain, Logger};
use slog_json::Json;
use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};
use std::time::Instant;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    let mut terminate = unix::signal(unix::SignalKind::terminate())?;
    let mut interrupt = unix::signal(unix::SignalKind::interrupt())?;
    let mut user_defined1 = unix::signal(unix::SignalKind::user_defined1())?;

    let config = Arc::new(Config::from_env());
    let bind_address = &config.bind_address.clone();
    let shutdown_timeout = config.shutdown_timeout.clone();

    let drain = Json::new(io::stderr())
        .add_default_keys()
        .set_pretty(cfg!(debug_assertions))
        .build()
        .filter_level(config.log_level);
    let safe_drain = Mutex::new(drain).map(slog::Fuse);
    let logger = Arc::new(Logger::root(safe_drain, o!("version" => VERSION)));

    let backend_client = Arc::new(BackendClient::new(
        &config.mor_backend_url,
        &logger.new(o!("scope" => "BackendClient")),
    ));
    let metrics = Arc::new(Metrics::new());
    let transcoder = Arc::new(TranscoderService::new(
        &config.path_to_ffmpeg,
        logger.new(o!("scope" => "TranscoderService")),
        metrics.clone(),
    ));
    let channel_player_factory = Arc::new(ChannelPlayerFactory::new(
        backend_client.clone(),
        transcoder.clone(),
        metrics.clone(),
        logger.new(o!("scope" => "ChannelPlayerFactory")),
    ));
    let channel_player_registry = Arc::new(ChannelPlayerRegistry::new());

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
                            let path = result.request().match_pattern();

                            match path.as_ref().map(String::as_str) {
                                Some("/metrics") | None => (),
                                Some(path) => metrics.update_http_request_total(
                                    &path,
                                    &method,
                                    status,
                                    instant.elapsed(),
                                ),
                            };

                            Ok(result)
                        }
                    }
                })
                .data(config.clone())
                .data(backend_client.clone())
                .data(logger.clone())
                .data(metrics.clone())
                .data(transcoder.clone())
                .data(channel_player_factory.clone())
                .data(channel_player_registry.clone())
                .service(listen_by_channel_id)
                .service(restart_by_channel_id)
                .service(get_active_streams)
                .service(get_metrics)
        }
    })
    .shutdown_timeout(shutdown_timeout)
    .bind(bind_address)?
    .run();

    info!(logger, "Application started");

    interrupt
        .recv()
        .or(terminate.recv())
        .or(user_defined1.recv())
        .await;

    info!(logger, "Received signal");

    server.stop(true).await;

    info!(logger, "Server stopped");

    Ok(())
}
