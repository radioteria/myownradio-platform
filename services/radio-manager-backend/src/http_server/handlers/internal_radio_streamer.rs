use crate::http_server::response::Response;
use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::{audio_tracks, streams};
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

    let stream = match streams::get_public_stream(&mut transaction, &stream_id)
        .await
        .tee_err(|error| {
            error!(?error, "Unable to get stream information");
        })? {
        Some(stream) => stream,
        None => {
            return Ok(HttpResponse::NotFound().finish());
        }
    };

    let tracks_duration =
        match audio_tracks::get_stream_audio_tracks_duration(&mut transaction, &stream_id)
            .await
            .tee_err(|error| {
                error!(?error, "Unable to count stream tracks duration");
            })? {
            0 => {
                error!("Stream tracklist has zero duration");

                return Ok(HttpResponse::Conflict().finish());
            }
            tracks_duration => tracks_duration,
        };

    let time_offset = match stream.calculate_time_offset(&params.timestamp, &tracks_duration) {
        Ok(offset) => offset,
        Err(TimeOffsetComputationError::UnexpectedStreamState) => {
            error!(?stream, "Unexpected stream entity state");

            return Ok(HttpResponse::Conflict().finish());
        }
        Err(TimeOffsetComputationError::StreamStopped) => {
            return Ok(HttpResponse::Conflict().finish());
        }
        Err(TimeOffsetComputationError::UnknownStreamStatus) => {
            error!(?stream.status, "Unknown stream status");

            return Ok(HttpResponse::Conflict().finish());
        }
    };

    let track_at_offset =
        match audio_tracks::get_audio_track_at_offset(&mut transaction, &stream_id, &time_offset)
            .await
            .tee_err(|error| {
                error!(?error, "Unable to get track at specified time offset");
            })? {
            Some(track_entry) => track_entry,
            None => {
                error!(?time_offset, "No track at specified time offset");

                return Ok(HttpResponse::Conflict().finish());
            }
        };

    let track_remainder = track_at_offset.remainder_at_time_position(time_offset);

    streams::seek_forward_user_stream(&mut transaction, &stream_id, track_remainder as i64)
        .await
        .tee_err(|error| {
            error!(?error, "Unable to skip track at specified time offset");
        })?;

    transaction.commit().await?;

    Ok(HttpResponse::Ok().finish())
}
