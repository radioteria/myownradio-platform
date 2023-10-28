use crate::data_structures::{StreamId, UserId};
use crate::http_server::response::Response;
use crate::pubsub_client::PubsubClient;
use actix_web::web::{Data, Json};
use actix_web::HttpResponse;
use serde::Deserialize;

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamStartedPayload {
    user_id: UserId,
    channel_id: StreamId,
    stream_id: String,
}

pub(crate) async fn handle_stream_started(
    payload: Json<StreamStartedPayload>,
    pubsub_client: Data<PubsubClient>,
) -> Response {
    pubsub_client
        .publish_outgoing_stream_started_message(
            &payload.channel_id,
            &payload.user_id,
            &payload.stream_id,
        )
        .await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamStatsPayload {
    user_id: UserId,
    channel_id: StreamId,
    stream_id: String,
    byte_count: u64,
    time_position: u64,
}

pub(crate) async fn handle_stream_stats(
    payload: Json<StreamStatsPayload>,
    pubsub_client: Data<PubsubClient>,
) -> Response {
    pubsub_client
        .publish_outgoing_stream_stats_message(
            &payload.channel_id,
            &payload.user_id,
            &payload.byte_count,
            &payload.time_position,
            &payload.stream_id,
        )
        .await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamFinishedPayload {
    user_id: UserId,
    channel_id: StreamId,
    stream_id: String,
}

pub(crate) async fn handle_stream_finished(
    payload: Json<StreamFinishedPayload>,
    pubsub_client: Data<PubsubClient>,
) -> Response {
    pubsub_client
        .publish_outgoing_stream_finished_message(
            &payload.channel_id,
            &payload.user_id,
            &payload.stream_id,
        )
        .await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamErrorPayload {
    user_id: UserId,
    channel_id: StreamId,
    stream_id: String,
}

pub(crate) async fn handle_stream_error(
    payload: Json<StreamErrorPayload>,
    pubsub_client: Data<PubsubClient>,
) -> Response {
    pubsub_client
        .publish_outgoing_stream_error_message(
            &payload.channel_id,
            &payload.user_id,
            &payload.stream_id,
        )
        .await?;

    Ok(HttpResponse::Ok().finish())
}
