use crate::models::types::StreamId;
use crate::repositories::streams::StreamsRepository;
use crate::Config;
use actix_web::{web, HttpResponse, Responder};
use tracing::error;

pub(crate) async fn get_stream_info(
    path: web::Path<StreamId>,
    streams_repository: web::Data<StreamsRepository>,
    config: web::Data<Config>,
) -> impl Responder {
    let stream_id = path.into_inner();

    let stream = match streams_repository.get_public_stream(&stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => return HttpResponse::NotFound().finish(),
        Err(error) => {
            error!(?error, "Unable to get stream information");

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
