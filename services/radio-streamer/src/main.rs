use crate::config::Config;
use crate::http::listen::listen_by_channel_id;
use crate::mor_backend_client::MorBackendClient;
use actix_web::web::Data;
use actix_web::{App, HttpServer};
use slog::{info, o, Drain, Fuse, Logger};
use slog_json::Json;
use std::io;
use std::io::Result;
use std::ops::Deref;
use std::sync::{Arc, Mutex};

mod config;
mod http;
mod mor_backend_client;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    let config = Arc::new(Config::from_env());

    let drain = Json::new(io::stderr())
        .add_default_keys()
        .set_pretty(cfg!(debug_assertions))
        .build()
        .filter_level(config.log_level);
    let safe_drain = Mutex::new(drain).map(slog::Fuse);
    let logger = Logger::root(safe_drain, o!("version" => VERSION));

    let mor_backend_client = Arc::new(MorBackendClient::new(&config.mor_backend_url, &logger));

    let bind_address = &config.bind_address.clone();

    info!(logger, "Starting server...");

    HttpServer::new(move || {
        App::new()
            .data(Arc::clone(&config))
            .data(Arc::clone(&mor_backend_client))
            .service(listen_by_channel_id)
    })
    .bind(bind_address)?
    .run()
    .await
    .map(|_| {
        info!(logger, "Server is started");
    })
}
