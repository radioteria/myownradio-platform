use crate::data_structures::{StreamId, UserId};
use crate::http_server::response::Response;
use crate::storage::db::repositories::streams;
use crate::storage::db::repositories::streams::get_user_streams_by_user_id;
use crate::MySqlClient;
use actix_web::web::{Data, Json, Path};
use actix_web::HttpResponse;
use serde::Deserialize;
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
                "coverBackground": row.cover_background,
                "rtmpUrl": row.rtmp_url,
                "rtmpStreamingKey": row.rtmp_streaming_key
            })
        })
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": streams_json,
    })))
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct RtmpParameters {
    rtmp_url: String,
    rtmp_streaming_key: String,
}

pub(crate) async fn update_rtmp_parameters(
    user_id: UserId,
    stream_id: Path<StreamId>,
    rtmp_parameters: Json<RtmpParameters>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    streams::update_channel_rtmp_parameters(
        &mut connection,
        &stream_id,
        &user_id,
        &rtmp_parameters.rtmp_url,
        &rtmp_parameters.rtmp_streaming_key,
    )
    .await?;

    Ok(HttpResponse::Ok().finish())
}
