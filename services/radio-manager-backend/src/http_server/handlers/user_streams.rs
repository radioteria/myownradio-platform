use crate::models::types::UserId;
use crate::repositories::streams::StreamsRepository;
use actix_web::web::Data;
use actix_web::{HttpResponse, Responder};
use slog::{error, Logger};

pub(crate) async fn get_user_streams(
    user_id: UserId,
    streams_repository: Data<StreamsRepository>,
    logger: Data<Logger>,
) -> impl Responder {
    let streams = match streams_repository.get_user_streams(&user_id).await {
        Ok(audio_tracks) => audio_tracks,
        Err(error) => {
            error!(logger, "Failed to get user streams"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": streams,
    }))
}
