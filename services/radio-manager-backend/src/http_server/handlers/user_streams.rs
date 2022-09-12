use crate::data_structures::UserId;
use crate::http_server::response::Response;
use crate::storage::db::repositories::streams::get_user_streams_by_user_id;
use crate::MySqlClient;
use actix_web::web::Data;
use actix_web::{HttpResponse, Responder};
use std::ops::DerefMut;
use tracing::error;

pub(crate) async fn get_user_streams(user_id: UserId, mysql_client: Data<MySqlClient>) -> Response {
    let mut connection = mysql_client.connection().await?;

    let stream_rows = match get_user_streams_by_user_id(&mut connection, &user_id).await {
        Ok(audio_tracks) => audio_tracks,
        Err(error) => {
            error!(?error, "Failed to get user streams");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    let streams_json: Vec<_> = stream_rows
        .into_iter()
        .map(|row| {
            serde_json::json!({
                "sid": row.sid,
                "name": row.name,
                "permalink": row.permalink,
                "info": row.info,
                "status": row.status,
                "access": row.access,
                "category": row.category,
                "hashtags": row.hashtags,
                "cover": row.cover,
                "cover_background": row.cover_background
            })
        })
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": streams_json,
    })))
}
