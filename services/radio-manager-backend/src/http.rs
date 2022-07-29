use crate::http_handlers::user_audio_tracks;
use crate::{repositories, MySqlClient};
use actix_server::Server;
use actix_web::web::Data;
use actix_web::{web, App, HttpServer};
use slog::Logger;
use std::io::Result;

pub(crate) fn run_server(
    bind_address: &str,
    mysql_client: &MySqlClient,
    logger: &Logger,
) -> Result<Server> {
    let logger = logger.clone();
    let mysql_client = mysql_client.clone();

    let audio_tracks_repository =
        repositories::audio_tracks::AudioTracksRepository::new(&mysql_client, &logger);

    let server = HttpServer::new(move || {
        App::new()
            .app_data(Data::new(mysql_client.clone()))
            .app_data(Data::new(logger.clone()))
            .app_data(Data::new(audio_tracks_repository.clone()))
            .service(
                web::scope("/v0/tracks")
                    .route("/", web::get().to(user_audio_tracks::get_user_audio_tracks)),
            )
    });

    Ok(server.workers(2).bind(bind_address)?.run())
}
