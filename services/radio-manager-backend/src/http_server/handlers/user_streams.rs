use crate::http_server::response::Response;
use crate::models::types::UserId;
use crate::repositories::streams;
use crate::MySqlClient;
use actix_web::web::Data;
use actix_web::{HttpResponse, Responder};
use std::ops::DerefMut;
use tracing::error;

pub(crate) async fn get_user_streams(user_id: UserId, mysql_client: Data<MySqlClient>) -> Response {
    let mut conn = mysql_client.connection().await?;

    let streams = match streams::get_user_streams(&mut conn, &user_id).await {
        Ok(audio_tracks) => audio_tracks,
        Err(error) => {
            error!(?error, "Failed to get user streams");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": streams,
    })))
}
