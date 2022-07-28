use crate::models::types::UserId;
use crate::repositories::audio_tracks::{AudioTracksRepository, SortingColumn, SortingOrder};
use actix_web::web::Data;
use actix_web::{web, HttpResponse, Responder};
use slog::{error, Logger};

pub(crate) async fn get_user_tracks(
    path: web::Path<UserId>,
    audio_tracks_repository: Data<AudioTracksRepository>,
    logger: Data<Logger>,
) -> impl Responder {
    let user_id = path.into_inner();
    let audio_tracks = match audio_tracks_repository
        .get_user_audio_tracks(
            &user_id,
            &None,
            &None,
            &0,
            &SortingColumn::TrackId,
            &SortingOrder::Asc,
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
