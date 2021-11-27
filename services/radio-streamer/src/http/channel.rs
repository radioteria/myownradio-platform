use crate::backend_client::BackendClient;
use crate::config::Config;
use crate::metrics::Metrics;
use crate::stream::player_loop::{make_player_loop, PlayerLoopError};
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpResponse, Responder};
use serde::Deserialize;
use slog::{error, Logger};
use std::sync::Arc;

#[derive(Deserialize, Clone)]
pub struct ListenQueryParams {
    format: Option<String>,
    client_id: Option<String>,
}

#[get("/channel-test/{channel_id}")]
pub(crate) async fn test_channel_playback(
    channel_id: web::Path<usize>,
    query_params: Query<ListenQueryParams>,
    backend_client: Data<Arc<BackendClient>>,
    logger: Data<Arc<Logger>>,
    metrics: Data<Arc<Metrics>>,
    config: Data<Arc<Config>>,
) -> impl Responder {
    let player_loop_events = match make_player_loop(
        &channel_id,
        &query_params.client_id,
        &config.path_to_ffmpeg,
        &backend_client,
        &logger,
        &metrics,
    )
    .await
    {
        Ok(player_loop_events) => player_loop_events,
        Err(PlayerLoopError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unexpected error on starting player loop"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().finish()
}
