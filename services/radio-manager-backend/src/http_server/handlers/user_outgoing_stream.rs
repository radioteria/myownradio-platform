use crate::data_structures::{StreamId, UserId};
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::services::auth::{AuthTokenClaim, AuthTokenClaims, AuthTokenService};
use crate::storage::db::repositories::streams;
use crate::web_egress_controller_client::{
    AudioSettings, RtmpSettings, VideoSettings, WebEgressControllerClient,
};
use actix_web::web::{Data, Path};
use actix_web::HttpResponse;
use serde_json::json;

pub(crate) async fn get_outgoing_stream(
    user_id: UserId,
    channel_id: Path<StreamId>,
    mysql_client: Data<MySqlClient>,
    web_egress_client: Data<WebEgressControllerClient>,
) -> Response {
    let status = web_egress_client.get_stream(&channel_id, &user_id).await?;

    Ok(HttpResponse::Ok().json(&status))
}

pub(crate) async fn start_outgoing_stream(
    user_id: UserId,
    channel_id: Path<StreamId>,
    mysql_client: Data<MySqlClient>,
    web_egress_client: Data<WebEgressControllerClient>,
    auth_token_service: Data<AuthTokenService>,
) -> Response {
    let mut conn = mysql_client.connection().await?;

    let stream_row = match streams::get_single_stream_by_id(&mut conn, &channel_id).await? {
        Some(row) => row,
        None => {
            return Ok(HttpResponse::NotFound().json(json!({ "error": "CHANNEL_NOT_FOUND" })));
        }
    };

    let now_timestamp = chrono::Utc::now().timestamp() as usize;
    let claims = AuthTokenClaims {
        user_id: user_id.clone(),
        // 1 week for testing purposes
        exp: now_timestamp + 604_800,
        claims: vec![AuthTokenClaim {
            methods: vec!["GET"],
            uris: vec!["/"],
        }],
    };
    let token = auth_token_service.sign_claims(claims);
    let stream_id = uuid::Uuid::new_v4().to_string();

    let rtmp_settings = RtmpSettings {
        rtmp_url: stream_row.rtmp_url,
        stream_key: stream_row.rtmp_streaming_key,
    };
    let video_settings = VideoSettings {
        width: 1280,
        height: 720,
        bitrate: 2500,
        framerate: 30,
    };
    let audio_settings = AudioSettings {
        bitrate: 256,
        channels: 2,
    };

    web_egress_client
        .start_stream(
            &channel_id,
            &user_id,
            &stream_id,
            &token,
            &rtmp_settings,
            &video_settings,
            &audio_settings,
        )
        .await?;

    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn stop_outgoing_stream(
    user_id: UserId,
    channel_id: Path<StreamId>,
    mysql_client: Data<MySqlClient>,
    web_egress_client: Data<WebEgressControllerClient>,
) -> Response {
    web_egress_client.stop_stream(&channel_id, &user_id).await?;

    Ok(HttpResponse::Ok().finish())
}
