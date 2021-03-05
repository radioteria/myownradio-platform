use crate::config::Config;
use crate::http::listen::listen;
use actix_web::{App, HttpServer};
use std::io::Result;
use std::sync::Arc;

mod config;
mod http;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");

#[actix_rt::main]
async fn main() -> Result<()> {
    let config = Arc::new(Config::from_env());

    HttpServer::new(|| App::new().service(listen))
        .bind(&config.bind_address)?
        .run()
        .await
}
