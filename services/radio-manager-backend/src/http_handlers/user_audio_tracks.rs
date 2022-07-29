use crate::models::types::UserId;
use crate::repositories::audio_tracks::{AudioTracksRepository, SortingColumn, SortingOrder};
use actix_web::web::Data;
use actix_web::{web, HttpResponse, Responder};
use serde::Deserialize;
use slog::{error, Logger};

#[derive(Deserialize)]
pub(crate) struct QueryParams {
    #[serde(default)]
    color_id: Option<u32>,
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
    path: web::Path<UserId>,
    query: web::Query<QueryParams>,
    audio_tracks_repository: Data<AudioTracksRepository>,
    logger: Data<Logger>,
) -> impl Responder {
    let user_id = path.into_inner();
    let params = query.into_inner();

    let audio_tracks = match audio_tracks_repository
        .get_user_audio_tracks(
            &user_id,
            &params.color_id,
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
