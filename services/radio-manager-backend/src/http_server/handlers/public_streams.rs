use crate::data_structures::StreamId;
use crate::http_server::response::Response;
use crate::storage::db::repositories::streams::get_single_stream_by_id;
use crate::{Config, MySqlClient};
use actix_web::{web, HttpResponse};
use tracing::error;

pub(crate) async fn get_stream_info(
    path: web::Path<StreamId>,
    config: web::Data<Config>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let stream_id = path.into_inner();

    let mut conn = mysql_client.connection().await?;

    let stream = match get_single_stream_by_id(&mut conn, &stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => return Ok(HttpResponse::NotFound().finish()),
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": {
            "name": stream.name,
            "status": stream.status,
        }
    })))
}
