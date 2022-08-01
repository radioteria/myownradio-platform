use crate::models::types::{StreamId, UserId};
use crate::repositories::audio_tracks::{AudioTracksRepository, SortingColumn, SortingOrder};
use crate::repositories::streams::StreamsRepository;
use actix_web::web::Data;
use actix_web::{web, HttpResponse, Responder};
use serde::Deserialize;
use slog::{error, Logger};

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
    audio_tracks_repository: Data<AudioTracksRepository>,
    logger: Data<Logger>,
) -> impl Responder {
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    let audio_tracks = match audio_tracks_repository
        .get_user_audio_tracks(
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
            error!(logger, "Failed to get user audio tracks"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": audio_tracks,
    }))
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
    audio_tracks_repository: Data<AudioTracksRepository>,
    streams_repository: Data<StreamsRepository>,
    logger: Data<Logger>,
) -> impl Responder {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    match streams_repository
        .get_single_user_stream(&user_id, &stream_id)
        .await
    {
        Ok(Some(_)) => (),
        Ok(None) => return HttpResponse::NotFound().finish(),
        Err(error) => {
            error!(logger, "Failed to get user stream"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    }

    let audio_tracks = match audio_tracks_repository
        .get_user_stream_audio_tracks(
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
            error!(logger, "Failed to get user playlist audio tracks"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": audio_tracks,
    }))
}
