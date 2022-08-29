use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::audio_tracks::AudioTracksRepository;
use crate::repositories::streams;
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
    audio_tracks_repository: web::Data<AudioTracksRepository>,
    mysql_client: web::Data<MySqlClient>,
) -> impl Responder {
    let params = query.into_inner();
    let stream_id = path.into_inner();

    let stream = match streams::get_public_stream(mysql_client.connection(), &stream_id)
        .await
        .tee_err(|error| {
            error!(?error, "Unable to get stream information");
        }) {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            return HttpResponse::InternalServerError().finish();
        }
    };

    let tracks_duration = match audio_tracks_repository
        .get_stream_audio_tracks_duration(&stream_id)
        .await
        .tee_err(|error| {
            error!(?error, "Unable to count stream tracks duration");
        }) {
        Ok(0) => {
            error!("Stream tracklist has zero duration");

            return HttpResponse::Conflict().finish();
        }
        Ok(tracks_duration) => tracks_duration,
        Err(error) => {
            return HttpResponse::InternalServerError().finish();
        }
    };

    let time_offset = match stream.calculate_time_offset(&params.timestamp, &tracks_duration) {
        Ok(offset) => offset,
        Err(TimeOffsetComputationError::UnexpectedStreamState) => {
            error!(?stream, "Unexpected stream entity state");

            return HttpResponse::Conflict().finish();
        }
        Err(TimeOffsetComputationError::StreamStopped) => {
            return HttpResponse::Conflict().finish();
        }
        Err(TimeOffsetComputationError::UnknownStreamStatus) => {
            error!(?stream.status, "Unknown stream status");

            return HttpResponse::Conflict().finish();
        }
    };

    let track_at_offset = match audio_tracks_repository
        .get_audio_track_at_offset(&stream_id, &time_offset)
        .await
        .tee_err(|error| {
            error!(?error, "Unable to get track at specified time offset");
        }) {
        Ok(Some(track_entry)) => track_entry,
        Ok(None) => {
            error!(?time_offset, "No track at specified time offset");

            return HttpResponse::Conflict().finish();
        }
        Err(error) => {
            return HttpResponse::InternalServerError().finish();
        }
    };

    let track_remainder = track_at_offset.remainder_at_time_position(time_offset);

    if let Err(_) = streams::seek_forward_user_stream(
        mysql_client.connection(),
        &stream_id,
        track_remainder as i64,
    )
    .await
    .tee_err(|error| {
        error!(?error, "Unable to skip track at specified time offset");
    }) {
        return HttpResponse::InternalServerError().finish();
    }

    HttpResponse::Ok().finish()
}
