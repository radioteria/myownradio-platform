use crate::config::Config;
use actix_server::Server;
use actix_web::{App, HttpServer};

pub(crate) fn run_server(config: &Config) -> std::io::Result<Server> {
    let server = HttpServer::new(move || App::new());

    Ok(server.workers(2).bind(&config.bind_address)?.run())
}
