extern crate core;

use actix_cors::Cors;
use actix_rt::signal::unix;
use actix_web::dev::Service;
use actix_web::http::Method;
use actix_web::web::Data;
use actix_web::{App, HttpServer};
use futures_lite::FutureExt;
use slog::{o, Drain, Logger};
use std::io;
use std::io::Result;
use std::sync::{Arc, Mutex};
use std::time::Instant;
use tracing::{error, info};

use crate::backend_client::BackendClient;
use crate::config::{Config, LogFormat};
use crate::http::channel::{
    get_active_channel_ids, get_channel_audio_stream_v2, restart_channel_by_id_v2,
};
use crate::http::metrics::get_metrics;
use crate::metrics::Metrics;
use crate::stream::StreamsRegistry;

mod audio_formats;
mod backend_client;
mod config;
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

    env_logger::init();
    myownradio_ffmpeg_utils::init().expect("Unable to initialize FFmpeg");

    let backend_client = Arc::new(BackendClient::new(
        &config.mor_backend_url,
        &logger.new(o!("scope" => "BackendClient")),
    ));
    let metrics = Arc::new(Metrics::new());

    let streams_registry = Arc::new(StreamsRegistry::new(&backend_client, &logger, &metrics));

    info!("Starting application...");

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
                .wrap(
                    Cors::default()
                        .allow_any_origin()
                        .allowed_methods(&[Method::GET])
                        .allowed_headers(["icy-metadata"])
                        .expose_headers(["icy-metaint", "icy-name", "icy-metadata"]),
                )
                .app_data(Data::new(config.clone()))
                .app_data(Data::new(backend_client.clone()))
                .app_data(Data::new(logger.clone()))
                .app_data(Data::new(metrics.clone()))
                .app_data(Data::new(streams_registry.clone()))
                .service(get_channel_audio_stream_v2)
                .service(restart_channel_by_id_v2)
                .service(get_active_channel_ids)
                .service(get_metrics)
        }
    })
    .shutdown_timeout(shutdown_timeout)
    .bind(bind_address)?
    .run();

    let server_handle = server.handle();

    actix_rt::spawn({
        let logger = logger.clone();

        async move {
            if let Err(error) = server.await {
                error!("Error on http server: {:?}", error);
            }
        }
    });

    info!("Application started");

    interrupt
        .recv()
        .or(terminate.recv())
        .or(user_defined1.recv())
        .await;

    info!("Received signal");

    server_handle.stop(true).await;

    info!("Server stopped");

    Ok(())
}
