mod config;
mod data_structures;
mod http_extractors;
mod http_server;
mod models;
mod mysql_client;
mod repositories;
mod system;

use crate::config::{Config, LogFormat};
use crate::mysql_client::MySqlClient;
use dotenv::dotenv;
use http_server::run_server;
use slog::{info, o, Drain, Logger};
use std::io;
use std::io::Result;
use std::sync::Mutex;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    dotenv().ok();

    let config = Config::from_env();

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

    let mysql_client = MySqlClient::new(&config.mysql, &logger)
        .await
        .expect("Unable to initialize MySQL client");

    let http_server = run_server(&config.bind_address, &mysql_client, &logger, &config)?;

    info!(logger, "Application started");

    http_server.await
}
