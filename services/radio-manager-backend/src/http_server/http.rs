use crate::http_server::handlers::{
    internal_radio_streamer, public_schedule, public_streams, user_audio_tracks, user_streams,
};
use crate::{repositories, Config, MySqlClient};
use actix_server::Server;
use actix_web::web::Data;
use actix_web::{web, App, HttpServer};
use std::io::Result;

pub(crate) fn run_server(
    bind_address: &str,
    mysql_client: &MySqlClient,
    config: &Config,
) -> Result<Server> {
    let mysql_client = mysql_client.clone();

    let audio_tracks_repository =
        repositories::audio_tracks::AudioTracksRepository::new(&mysql_client);
    let streams_repository = repositories::streams::StreamsRepository::new(&mysql_client);

    let config = config.clone();

    let server = HttpServer::new(move || {
        App::new()
            .app_data(Data::new(mysql_client.clone()))
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
            .service(
                web::scope("/pub/v0/streams/{stream_id}")
                    .route(
                        "/now-playing",
                        web::get().to(public_schedule::get_now_playing),
                    )
                    .route(
                        "/current-track",
                        web::get().to(public_schedule::get_current_track),
                    )
                    .route("/info", web::get().to(public_streams::get_stream_info)),
            )
            .service(web::scope("/radio-streamer").route(
                "/streams/{stream_id}/skip-current-track",
                web::post().to(internal_radio_streamer::skip_current_track),
            ))
    });

    Ok(server.workers(2).bind(bind_address)?.run())
}
