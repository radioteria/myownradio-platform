//
// Exposed endpoints:
//
// @todo GET /user/${user_id}/tracks (get list of user tracks)
// @todo GET /user/${user_id}/tracks/${track_id}/preview (preview audio)
// @todo POST /user/${user_id}/tracks (upload audio track)
// @todo DELETE /user/${user_id}/tracks/${track_id} (delete audio track)
// @todo PUT /user/${user_id}/tracks/${track_id}/metadata (update track metadata)
//

mod config;
mod http;
mod system;

use crate::config::{Config, LogFormat};
use crate::http::run_server;
use dotenv::dotenv;
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

    let http_server = run_server(&config.bind_address)?;

    info!(logger, "Application started");

    http_server.await
}
