use actix_rt::signal::unix;
use actix_web::dev::Service;
use actix_web::{App, HttpServer};
use futures_lite::FutureExt;
use slog::{info, o, Drain, Logger};
use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};
use std::time::Instant;

use crate::backend_client::BackendClient;
use crate::config::{Config, LogFormat};
use crate::http::channel::{get_active_channel_ids, listen_channel, restart_by_channel_id};
use crate::http::metrics::get_metrics;
use crate::metrics::Metrics;
use crate::stream::encoder_registry::EncoderRegistry;
use crate::stream::player_registry::PlayerRegistry;

mod audio_formats;
mod backend_client;
mod config;
mod helpers;
mod http;
mod macros;
mod metrics;
mod stream;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    let mut terminate = unix::signal(unix::SignalKind::terminate())?;
    let mut interrupt = unix::signal(unix::SignalKind::interrupt())?;
    let mut user_defined1 = unix::signal(unix::SignalKind::user_defined1())?;

    let config = Arc::new(Config::from_env());
    let bind_address = &config.bind_address.clone();
    let shutdown_timeout = config.shutdown_timeout.clone();

    let logger = match config.log_format {
        LogFormat::Json => {
            let drain = slog_json::Json::new(io::stderr())
                .add_default_keys()
                .set_pretty(cfg!(debug_assertions))
                .build()
                .filter_level(config.log_level);
            let safe_drain = Mutex::new(drain).map(slog::Fuse);
            Logger::root(safe_drain, o!("version" => VERSION))
        }
        LogFormat::Term => {
            let drain = slog_term::FullFormat::new(slog_term::TermDecorator::new().build())
                .build()
                .filter_level(config.log_level);
            let safe_drain = Mutex::new(drain).map(slog::Fuse);
            Logger::root(safe_drain, o!("version" => VERSION))
        }
    };
    let logger = Arc::new(logger);

    let backend_client = Arc::new(BackendClient::new(
        &config.mor_backend_url,
        &logger.new(o!("scope" => "BackendClient")),
    ));
    let metrics = Arc::new(Metrics::new());

    let player_registry = PlayerRegistry::new(
        config.path_to_ffmpeg.clone(),
        backend_client.clone(),
        logger.new(o!("service" => "PlayerRegistry")),
        metrics.clone(),
    );

    let encoder_registry = EncoderRegistry::new(
        config.path_to_ffmpeg.clone(),
        logger.new(o!("service" => "EncoderRegistry")),
        metrics.clone(),
        player_registry.clone(),
    );

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
                .data(player_registry.clone())
                .data(encoder_registry.clone())
                .service(listen_channel)
                .service(restart_by_channel_id)
                .service(get_active_channel_ids)
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
