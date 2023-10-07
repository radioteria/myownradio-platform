use crate::data_structures::{OrderId, StreamId, UserId};
use crate::http_server::response::Response;
use crate::services::StreamServiceFactory;
use actix_web::{web, HttpResponse};
use chrono::Duration;
use serde::Deserialize;

#[derive(Deserialize)]
pub(crate) struct PlayPathParams {
    pub(crate) stream_id: StreamId,
}

pub(crate) async fn play(
    params: web::Path<PlayPathParams>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_service = stream_service_factory
        .create_service_for_user(&params.stream_id, &user_id)
        .await?;

    stream_service.play().await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
pub(crate) struct PausePathParams {
    pub(crate) stream_id: StreamId,
}

pub(crate) async fn pause(
    params: web::Path<PausePathParams>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_service = stream_service_factory
        .create_service_for_user(&params.stream_id, &user_id)
        .await?;

    stream_service.pause().await?;

    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn stop(
    params: web::Path<StreamId>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_id = params.into_inner();
    let stream_service = stream_service_factory
        .create_service_for_user(&stream_id, &user_id)
        .await?;

    stream_service.stop().await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
pub(crate) struct SeekPathParams {
    pub(crate) stream_id: StreamId,
    pub(crate) position: i64,
}

pub(crate) async fn seek(
    params: web::Path<SeekPathParams>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_service = stream_service_factory
        .create_service_for_user(&params.stream_id, &user_id)
        .await?;

    stream_service
        .seek(&Duration::milliseconds(params.position))
        .await?;

    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn play_next(
    params: web::Path<StreamId>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_id = params.into_inner();
    let stream_service = stream_service_factory
        .create_service_for_user(&stream_id, &user_id)
        .await?;

    stream_service.play_next().await?;

    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn play_prev(
    params: web::Path<StreamId>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let stream_id = params.into_inner();
    let stream_service = stream_service_factory
        .create_service_for_user(&stream_id, &user_id)
        .await?;

    stream_service.play_prev().await?;

    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn play_from(
    params: web::Path<(StreamId, OrderId)>,
    stream_service_factory: web::Data<StreamServiceFactory>,
    user_id: UserId,
) -> Response {
    let (stream_id, playlist_position) = params.into_inner();
    let stream_service = stream_service_factory
        .create_service_for_user(&stream_id, &user_id)
        .await?;

    stream_service
        .play_from_playlist_position(&playlist_position)
        .await?;

    Ok(HttpResponse::Ok().finish())
}
