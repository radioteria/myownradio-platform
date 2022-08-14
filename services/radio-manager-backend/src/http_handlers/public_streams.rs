use crate::models::types::StreamId;
use crate::repositories::streams::StreamsRepository;
use crate::Config;
use actix_web::{web, HttpResponse, Responder};
use slog::{error, Logger};

pub(crate) async fn get_stream_info(
    path: web::Path<StreamId>,
    streams_repository: web::Data<StreamsRepository>,
    logger: web::Data<Logger>,
    config: web::Data<Config>,
) -> impl Responder {
    let stream_id = path.into_inner();

    let stream = match streams_repository.get_public_stream(&stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => return HttpResponse::NotFound().finish(),
        Err(error) => {
            error!(logger, "Unable to get stream information"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": {
            "name": stream.name,
            "status": stream.status,
        }
    }))
}
