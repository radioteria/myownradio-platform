use crate::http_server::handlers::{
    forward_auth, internal_egress_process, internal_radio_streamer, public_auth_v0,
    public_schedule, public_streams, user_audio_stream, user_audio_tracks, user_audio_tracks_v2,
    user_outgoing_stream, user_stream_control, user_stream_destinations, user_streams,
};
use crate::pubsub_client::PubsubClient;
use crate::services::auth::{AuthService, AuthTokenService};
use crate::storage::fs::FileSystem;
use crate::web_egress_controller_client::WebEgressControllerClient;
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
    pubsub_client: PubsubClient,
    auth_token_service: AuthTokenService,
    web_egress_controller_client: WebEgressControllerClient,
    auth_service: AuthService,
) -> Result<Server> {
    let mysql_client = mysql_client.clone();

    let config = config.clone();

    let server = HttpServer::new(move || {
        App::new()
            .app_data(Data::new(mysql_client.clone()))
            .app_data(Data::new(config.clone()))
            .app_data(Data::new(file_system.clone()))
            .app_data(Data::new(stream_service_factory.clone()))
            .app_data(Data::new(pubsub_client.clone()))
            .app_data(Data::new(auth_token_service.clone()))
            .app_data(Data::new(auth_service.clone()))
            .app_data(Data::new(web_egress_controller_client.clone()))
            .service(
                web::scope("/pub")
                    .service(
                        web::scope("/v0/auth")
                            .service(public_auth_v0::login)
                            .service(public_auth_v0::logout)
                            .service(public_auth_v0::signup)
                            .service(public_auth_v0::reset_password)
                            .service(public_auth_v0::request_password_reset)
                            .service(public_auth_v0::confirm_email),
                    )
                    .service(
                        web::scope("/v0/streams")
                            .service(
                                web::resource("/{stream_id}/outgoing-stream")
                                    .route(web::get().to(user_outgoing_stream::get_outgoing_stream))
                                    .route(
                                        web::post().to(user_outgoing_stream::start_outgoing_stream),
                                    )
                                    .route(
                                        web::delete()
                                            .to(user_outgoing_stream::stop_outgoing_stream),
                                    ),
                            )
                            .route(
                                "/{stream_id}/now-playing",
                                web::get().to(public_schedule::get_now_playing),
                            )
                            .route(
                                "/{stream_id}/current-track",
                                web::get().to(public_schedule::get_current_track),
                            )
                            .route(
                                "/{stream_id}/info",
                                web::get().to(public_streams::get_stream_info),
                            ),
                    ),
            )
            .service(web::scope("/v0/forward-auth").route(
                "/by-token",
                web::get().to(forward_auth::auth_by_jwt_token_or_legacy_token),
            ))
            .service(
                web::scope("/v1/tracks")
                    .route(
                        "/all",
                        web::get().to(user_audio_tracks_v2::get_user_audio_tracks),
                    )
                    .route(
                        "/unused",
                        web::get().to(user_audio_tracks_v2::get_unused_user_audio_tracks),
                    )
                    .route(
                        "/channel/{channel_id}",
                        web::get().to(user_audio_tracks_v2::get_channel_audio_tracks),
                    ),
            )
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
                        web::get().to(user_audio_stream::transcode_audio_track),
                    )
                    .route(
                        "/{track_id}/download",
                        web::get().to(user_audio_tracks::download_audio_track::<FS>),
                    ),
            )
            .service(web::scope("/v0/streams/{stream_id}/tracks").route(
                "/",
                web::get().to(user_audio_tracks::get_user_stream_audio_tracks),
            ))
            .service(
                web::scope("/v0/streams/{stream_id}/controls")
                    .route("/play", web::post().to(user_stream_control::play))
                    .route("/pause", web::post().to(user_stream_control::pause))
                    .route("/stop", web::post().to(user_stream_control::stop))
                    .route(
                        "/seek/{position}",
                        web::post().to(user_stream_control::seek),
                    )
                    .route("/play-next", web::post().to(user_stream_control::play_next))
                    .route("/play-prev", web::post().to(user_stream_control::play_prev))
                    .route(
                        "/play-from/{playlist_position}",
                        web::post().to(user_stream_control::play_from),
                    ),
            )
            .service(
                web::resource("/v0/streams/{channel_id}/outgoing-stream")
                    .route(web::get().to(user_outgoing_stream::get_outgoing_stream))
                    .route(web::delete().to(user_outgoing_stream::stop_outgoing_stream))
                    .route(web::post().to(user_outgoing_stream::start_outgoing_stream)),
            )
            .service(
                web::scope("/v0/streams")
                    .route("/", web::get().to(user_streams::get_user_streams))
                    .route(
                        "/{stream_id}/rtmp-settings",
                        web::post().to(user_streams::update_rtmp_settings),
                    ),
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
            .service(
                web::scope("/internal/web-egress-process")
                    .route(
                        "/v0/stream-started",
                        web::post().to(internal_egress_process::handle_stream_started),
                    )
                    .route(
                        "/v0/stream-finished",
                        web::post().to(internal_egress_process::handle_stream_finished),
                    )
                    .route(
                        "/v0/stream-stats",
                        web::post().to(internal_egress_process::handle_stream_stats),
                    )
                    .route(
                        "/v0/stream-error",
                        web::post().to(internal_egress_process::handle_stream_error),
                    ),
            )
            .service(
                web::scope("/v0/destinations")
                    .route(
                        "/",
                        web::get().to(user_stream_destinations::get_stream_destinations),
                    )
                    .route(
                        "/create-for-channel/{channel_id}",
                        web::post().to(user_stream_destinations::create_stream_destination),
                    )
                    .route(
                        "/{id}",
                        web::put().to(user_stream_destinations::update_stream_destination),
                    )
                    .route(
                        "/{id}",
                        web::delete().to(user_stream_destinations::delete_stream_destination),
                    ),
            )
    });

    Ok(server.workers(2).bind(bind_address)?.run())
}
