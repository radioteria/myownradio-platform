use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::audio_tracks::AudioTracksRepository;
use crate::repositories::streams::StreamsRepository;
use actix_web::{web, HttpResponse, Responder};
use serde::Deserialize;
use slog::{error, Logger};
use sqlx::{query, Result};

#[derive(Deserialize)]
pub(crate) struct SkipCurrentTrackQuery {
    #[serde(rename = "ts")]
    timestamp: i64,
}

pub(crate) async fn skip_current_track(
    query: web::Query<SkipCurrentTrackQuery>,
    path: web::Path<StreamId>,
    streams_repository: web::Data<StreamsRepository>,
    audio_tracks_repository: web::Data<AudioTracksRepository>,
    logger: web::Data<Logger>,
) -> impl Responder {
    let params = query.into_inner();
    let stream_id = path.into_inner();

    let stream = match streams_repository.get_public_stream(&stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unable to get stream information"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let tracks_duration = match audio_tracks_repository
        .get_stream_audio_tracks_duration(&stream_id)
        .await
    {
        Ok(0) => {
            error!(logger, "Stream tracklist has zero duration");

            return HttpResponse::Conflict().finish();
        }
        Ok(tracks_duration) => tracks_duration,
        Err(error) => {
            error!(logger, "Unable to count stream tracks duration"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let time_offset = match stream.calculate_time_offset(&params.timestamp, &tracks_duration) {
        Ok(offset) => offset,
        Err(TimeOffsetComputationError::UnexpectedStreamState) => {
            error!(logger, "Unexpected stream entity state"; "stream" => ?stream);

            return HttpResponse::Conflict().finish();
        }
        Err(TimeOffsetComputationError::StreamStopped) => {
            return HttpResponse::Conflict().finish();
        }
        Err(TimeOffsetComputationError::UnknownStreamStatus) => {
            error!(logger, "Unknown stream status"; "status" => ?stream.status);

            return HttpResponse::Conflict().finish();
        }
    };

    let track_at_offset = match audio_tracks_repository
        .get_audio_track_at_offset(&stream_id, &time_offset)
        .await
    {
        Ok(Some(track_entry)) => track_entry,
        Ok(None) => {
            error!(logger, "No track at specified time offset"; "offset" => ?time_offset);

            return HttpResponse::Conflict().finish();
        }
        Err(error) => {
            error!(logger, "Unable to get track at specified time offset"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let track_remainder = track_at_offset.remainder_at_time_position(time_offset);

    if let Err(error) = streams_repository
        .seek_forward_user_stream(&stream_id, track_remainder as i64)
        .await
    {
        error!(logger, "Unable to skip track at specified time offset"; "error" => ?error);

        return HttpResponse::InternalServerError().finish();
    }

    HttpResponse::Ok().finish()
}
