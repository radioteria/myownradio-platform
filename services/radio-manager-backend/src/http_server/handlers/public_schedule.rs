use crate::models::audio_track::StreamTracksEntry;
use crate::models::stream::StreamStatus;
use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::{audio_tracks, streams};
use crate::{Config, MySqlClient};
use actix_web::middleware::Logger;
use actix_web::{web, HttpResponse, Responder};
use serde::{Deserialize, Serialize};
use std::time::{SystemTime, UNIX_EPOCH};
use tracing::error;

#[derive(Clone, Serialize)]
pub(crate) struct StreamTracksEntryWithPosition {
    #[serde(flatten)]
    pub(crate) track_entry: StreamTracksEntry,
    pub(crate) position: i64,
}

pub(crate) async fn get_current_track(
    path: web::Path<StreamId>,
    config: web::Data<Config>,
    mysql_client: web::Data<MySqlClient>,
) -> impl Responder {
    let stream_id = path.into_inner();
    let timestamp = SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .unwrap()
        .as_millis() as i64;

    let stream = match streams::get_public_stream(mysql_client.connection(), &stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return HttpResponse::InternalServerError().finish();
        }
    };

    let tracks_duration =
        match audio_tracks::get_stream_audio_tracks_duration(mysql_client.connection(), &stream_id)
            .await
        {
            Ok(0) => {
                error!("Stream tracks list has zero duration");

                return HttpResponse::Conflict().finish();
            }
            Ok(tracks_duration) => tracks_duration,
            Err(error) => {
                error!(?error, "Unable to count stream tracks duration");

                return HttpResponse::InternalServerError().finish();
            }
        };

    let time_offset = match stream.calculate_time_offset(&timestamp, &tracks_duration) {
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

    let tracks = match audio_tracks::get_current_and_next_audio_tracks_at_offset(
        mysql_client.connection(),
        &stream_id,
        &time_offset,
    )
    .await
    {
        Ok(tracks) => tracks,
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return HttpResponse::InternalServerError().finish();
        }
    };

    match tracks {
        Some((current_track, _)) => HttpResponse::Ok().json(serde_json::json!({
            "code": 1i32,
            "message": "OK",
            "data": StreamTracksEntryWithPosition {
                position: time_offset - (current_track.time_offset as i64),
                track_entry: current_track,
            },
        })),
        None => HttpResponse::Conflict().finish(),
    }
}

#[derive(Deserialize)]
pub(crate) struct GetNowPlayingQuery {
    #[serde(rename = "ts")]
    timestamp: i64,
}

pub(crate) async fn get_now_playing(
    path: web::Path<StreamId>,
    query: web::Query<GetNowPlayingQuery>,
    config: web::Data<Config>,
    mysql_client: web::Data<MySqlClient>,
) -> impl Responder {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let stream = match streams::get_public_stream(mysql_client.connection(), &stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return HttpResponse::InternalServerError().finish();
        }
    };

    let tracks_duration =
        match audio_tracks::get_stream_audio_tracks_duration(mysql_client.connection(), &stream_id)
            .await
        {
            Ok(0) => {
                error!("Stream tracks list has zero duration");

                return HttpResponse::Conflict().finish();
            }
            Ok(tracks_duration) => tracks_duration,
            Err(error) => {
                error!(?error, "Unable to count stream tracks duration");

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

    let tracks = match audio_tracks::get_current_and_next_audio_tracks_at_offset(
        mysql_client.connection(),
        &stream_id,
        &time_offset,
    )
    .await
    {
        Ok(tracks) => tracks,
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return HttpResponse::InternalServerError().finish();
        }
    };

    match tracks {
        Some((current_track, next_track)) => HttpResponse::Ok().json(serde_json::json!({
            "code": 1i32,
            "message": "OK",
            "data": {
                "time": params.timestamp,
                "playlist_position": current_track.t_order,
                "current_track": {
                    "offset": time_offset - (current_track.time_offset as i64),
                    "title": current_track.track.artist_and_title(),
                    "url": format!("{}audio/{}", config.file_server_endpoint, current_track.track.file_path()),
                    "duration": current_track.track.duration,
                },
                "next_track": {
                    "title": next_track.track.artist_and_title(),
                    "url": format!("{}audio/{}", config.file_server_endpoint, next_track.track.file_path()),
                    "duration": next_track.track.duration,
                },
            },
        })),
        None => HttpResponse::Conflict().finish(),
    }
}
