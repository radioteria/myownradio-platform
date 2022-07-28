use actix_server::Server;
use actix_web::{App, HttpServer};
use std::io::Result;

pub(crate) fn run_server(bind_address: &str) -> Result<Server> {
    let server = HttpServer::new(|| App::new());

    Ok(server.workers(2).bind(bind_address)?.run())
}
