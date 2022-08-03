use crate::models::stream::StreamStatus;
use crate::models::types::StreamId;
use crate::repositories::audio_tracks::AudioTracksRepository;
use crate::repositories::streams::StreamsRepository;
use actix_web::{web, HttpResponse, Responder};
use serde::Deserialize;
use slog::{error, Logger};

#[derive(Deserialize)]
pub(crate) struct GetNowPlayingQuery {
    #[serde(rename = "ts")]
    timestamp: i64,
}

pub(crate) async fn get_now_playing(
    path: web::Path<StreamId>,
    query: web::Query<GetNowPlayingQuery>,
    audio_tracks_repository: web::Data<AudioTracksRepository>,
    streams_repository: web::Data<StreamsRepository>,
    logger: web::Data<Logger>,
) -> impl Responder {
    let stream_id = path.into_inner();
    let params = query.into_inner();

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

    let time_offset_with_overflow = match (&stream.status, &stream.started, &stream.started_from) {
        (&StreamStatus::Playing, Some(started), Some(started_from)) => {
            (params.timestamp - started) + started_from
        }
        (&StreamStatus::Playing, _, _) => {
            error!(logger, "Unexpected stream entity state"; "stream" => ?stream);

            return HttpResponse::Conflict().finish();
        }
        (&StreamStatus::Stopped, _, _) => {
            return HttpResponse::Conflict().finish();
        }
        (&StreamStatus::Unknown, _, _) => {
            error!(logger, "Unknown stream status"; "status" => ?stream.status);

            return HttpResponse::Conflict().finish();
        }
    };

    let tracks_duration = match audio_tracks_repository
        .get_stream_audio_tracks_duration(&stream_id)
        .await
    {
        Ok(tracks_duration) => tracks_duration,
        Err(error) => {
            error!(logger, "Unable to count stream tracks duration"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let time_offset = (time_offset_with_overflow % tracks_duration) as i32;

    let tracks = match audio_tracks_repository
        .get_current_and_next_audio_tracks_at_offset(&stream_id, &time_offset)
        .await
    {
        Ok(tracks) => tracks,
        Err(error) => {
            error!(logger, "Unable to get stream information"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(tracks)
}
