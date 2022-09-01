use crate::http_server::response::Response;
use crate::models::types::{StreamId, UserId};
use crate::repositories::audio_tracks::{SortingColumn, SortingOrder};
use crate::repositories::{audio_tracks, stream_audio_tracks, streams};
use crate::MySqlClient;
use actix_web::web::{Data, Form};
use actix_web::{web, HttpRequest, HttpResponse, Responder};
use serde::Deserialize;
use tracing::error;

#[derive(Deserialize)]
pub(crate) struct GetUserAudioTracksQuery {
    #[serde(default)]
    color_id: Option<String>,
    #[serde(default)]
    filter: Option<String>,
    #[serde(default)]
    offset: u32,
    #[serde(default)]
    unused: bool,
    #[serde(default)]
    row: SortingColumn,
    #[serde(default)]
    order: SortingOrder,
}

pub(crate) async fn get_user_audio_tracks(
    user_id: UserId,
    query: web::Query<GetUserAudioTracksQuery>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    let mut conn = mysql_client.connection().await?;

    let audio_tracks = match audio_tracks::get_user_audio_tracks(
        &mut conn,
        &user_id,
        &color_id,
        &params.filter,
        &params.offset,
        &params.unused,
        &params.row,
        &params.order,
    )
    .await
    {
        Ok(audio_tracks) => audio_tracks,
        Err(error) => {
            error!(?error, "Failed to get user audio tracks");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": audio_tracks,
    })))
}

#[derive(Deserialize)]
pub(crate) struct GetUserPlaylistAudioTracksQuery {
    #[serde(default)]
    color_id: Option<String>,
    #[serde(default)]
    filter: Option<String>,
    #[serde(default)]
    offset: u32,
}

pub(crate) async fn get_user_stream_audio_tracks(
    path: web::Path<StreamId>,
    user_id: UserId,
    query: web::Query<GetUserPlaylistAudioTracksQuery>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    let mut conn = mysql_client.connection().await?;

    match streams::get_single_user_stream(&mut conn, &user_id, &stream_id).await {
        Ok(Some(_)) => (),
        Ok(None) => return Ok(HttpResponse::NotFound().finish()),
        Err(error) => {
            error!(?error, "Failed to get user stream");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    }

    let audio_tracks = match stream_audio_tracks::get_user_stream_audio_tracks(
        &mut conn,
        &user_id,
        &stream_id,
        &color_id,
        &params.filter,
        &params.offset,
    )
    .await
    {
        Ok(audio_tracks) => audio_tracks,
        Err(error) => {
            error!(?error, "Failed to get user playlist audio tracks");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": audio_tracks,
    })))
}

#[derive(Deserialize)]
pub(crate) struct UploadedFile {}

#[derive(Deserialize)]
pub(crate) struct UploadAudioTrackForm {
    #[serde(default)]
    stream_id: Option<StreamId>,
    #[serde(default)]
    up_next: bool,
    file: UploadedFile,
}

pub(crate) async fn upload_audio_track(
    user_id: UserId,
    form: Form<UploadAudioTrackForm>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    Ok(HttpResponse::Ok().finish())
}
