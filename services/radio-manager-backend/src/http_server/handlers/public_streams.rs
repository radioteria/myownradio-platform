use crate::models::types::StreamId;
use crate::repositories::streams;
use crate::{Config, MySqlClient};
use actix_web::{web, HttpResponse, Responder};
use tracing::error;

pub(crate) async fn get_stream_info(
    path: web::Path<StreamId>,
    config: web::Data<Config>,
    mysql_client: web::Data<MySqlClient>,
) -> impl Responder {
    let stream_id = path.into_inner();

    let stream = match streams::get_public_stream(mysql_client.connection(), &stream_id).await {
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
