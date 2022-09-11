use crate::http_server::response::Response;
use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::{stream_audio_tracks, streams};
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_stream_playlist_duration,
};
use crate::storage::db::repositories::StreamStatus;
use crate::utils::TeeResultUtils;
use crate::MySqlClient;
use actix_web::{web, HttpResponse, Responder};
use serde::Deserialize;
use sqlx::{query, Result};
use tracing::error;

#[derive(Deserialize)]
pub(crate) struct SkipCurrentTrackQuery {
    #[serde(rename = "ts")]
    timestamp: i64,
}

pub(crate) async fn skip_current_track(
    query: web::Query<SkipCurrentTrackQuery>,
    path: web::Path<StreamId>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let params = query.into_inner();
    let stream_id = path.into_inner();
    let mut transaction = mysql_client.transaction().await?;

    let stream = match get_single_stream_by_id(&mut transaction, &stream_id)
        .await
        .tee_err(|error| error!(?error, "Unable to get stream information"))?
    {
        Some(stream) => stream,
        None => {
            return Ok(HttpResponse::NotFound().finish());
        }
    };

    let playlist_duration = get_stream_playlist_duration(&mut connection, &stream_id)
        .await
        .tee_err(|error| error!("Unable to get stream playlist duration"))?;

    if let (StreamStatus::Playing, Some(started_at), Some(started_from)) =
        (&stream.status, &stream.started, &stream.started_from)
    {}

    todo!()
}
