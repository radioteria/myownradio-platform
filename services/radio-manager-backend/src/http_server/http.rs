use crate::http_server::handlers::{
    internal_radio_streamer, public_schedule, public_streams, user_audio_tracks,
    user_stream_control, user_streams,
};
use crate::storage::fs::FileSystem;
use crate::{Config, MySqlClient, StreamServiceFactory};
use actix_server::Server;
use actix_web::web::Data;
use actix_web::{web, App, HttpServer};
use std::io::Result;

pub(crate) fn run_server<FS: FileSystem + Send + Sync + Clone + 'static>(
    bind_address: &str,
    mysql_client: MySqlClient,
    config: Config,
    file_system: FS,
    stream_service_factory: StreamServiceFactory,
) -> Result<Server> {
    let mysql_client = mysql_client.clone();

    let config = config.clone();

    let server = HttpServer::new(move || {
        App::new()
            .app_data(Data::new(mysql_client.clone()))
            .app_data(Data::new(config.clone()))
            .app_data(Data::new(file_system.clone()))
            .app_data(Data::new(stream_service_factory.clone()))
            .service(
                web::scope("/v0/tracks")
                    .route("/", web::get().to(user_audio_tracks::get_user_audio_tracks))
                    .route("/", web::post().to(user_audio_tracks::upload_audio_track))
                    .route(
                        "/{track_id}",
                        web::delete().to(user_audio_tracks::delete_audio_track::<FS>),
                    )
                    .route(
                        "/{track_id}/transcode",
                        web::post().to(user_audio_tracks::transcode_audio_track),
                    ),
            )
            .service(web::scope("/v0/streams/{stream_id}/tracks").route(
                "/",
                web::get().to(user_audio_tracks::get_user_stream_audio_tracks),
            ))
            .service(
                web::scope("/v0/streams/{stream_id}/controls")
                    .route("/play", web::post().to(user_stream_control::play))
                    .route("/stop", web::post().to(user_stream_control::stop))
                    .route("/play-next", web::post().to(user_stream_control::play_next))
                    .route("/play-prev", web::post().to(user_stream_control::play_prev))
                    .route(
                        "/play-from/{order_id}",
                        web::post().to(user_stream_control::play_from),
                    ),
            )
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
            .service(
                web::scope("/internal/radio-streamer")
                    .route(
                        "/v0/streams/{stream_id}/playing-at/{unix_time}",
                        web::get().to(internal_radio_streamer::get_playing_at),
                    )
                    .route(
                        "/v0/streams/{stream_id}/skip-track",
                        web::post().to(internal_radio_streamer::skip_track),
                    ),
            )
    });

    Ok(server.workers(2).bind(bind_address)?.run())
}
