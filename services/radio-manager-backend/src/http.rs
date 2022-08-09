use crate::http_handlers::{public_schedule, user_audio_tracks, user_streams};
use crate::{repositories, Config, MySqlClient};
use actix_server::Server;
use actix_web::web::Data;
use actix_web::{web, App, HttpServer};
use slog::Logger;
use std::io::Result;

pub(crate) fn run_server(
    bind_address: &str,
    mysql_client: &MySqlClient,
    logger: &Logger,
    config: &Config,
) -> Result<Server> {
    let logger = logger.clone();
    let mysql_client = mysql_client.clone();

    let audio_tracks_repository =
        repositories::audio_tracks::AudioTracksRepository::new(&mysql_client, &logger);
    let streams_repository = repositories::streams::StreamsRepository::new(&mysql_client, &logger);

    let config = config.clone();

    let server = HttpServer::new(move || {
        App::new()
            .app_data(Data::new(mysql_client.clone()))
            .app_data(Data::new(logger.clone()))
            .app_data(Data::new(audio_tracks_repository.clone()))
            .app_data(Data::new(streams_repository.clone()))
            .app_data(Data::new(config.clone()))
            .service(
                web::scope("/v0/tracks")
                    .route("/", web::get().to(user_audio_tracks::get_user_audio_tracks)),
            )
            .service(web::scope("/v0/streams/{stream_id}/tracks").route(
                "/",
                web::get().to(user_audio_tracks::get_user_stream_audio_tracks),
            ))
            .service(
                web::scope("/v0/streams").route("/", web::get().to(user_streams::get_user_streams)),
            )
            .service(web::scope("/pub/v0/streams/{stream_id}").route(
                "/now-playing",
                web::get().to(public_schedule::get_now_playing),
            ))
    });

    Ok(server.workers(2).bind(bind_address)?.run())
}
