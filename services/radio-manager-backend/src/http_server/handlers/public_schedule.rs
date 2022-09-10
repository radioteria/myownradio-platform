use crate::http_server::response::Response;
use crate::models::audio_track::StreamTracksEntry;
use crate::models::stream::StreamStatus;
use crate::models::stream_ext::{TimeOffsetComputationError, TimeOffsetWithOverflow};
use crate::models::types::StreamId;
use crate::repositories::{stream_audio_tracks, streams};
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
) -> Response {
    let stream_id = path.into_inner();
    let timestamp = SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .unwrap()
        .as_millis() as i64;

    let mut connection = mysql_client.connection().await?;

    let stream = match streams::get_public_stream(&mut connection, &stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return Ok(HttpResponse::NotFound().finish());
        }
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    let tracks_duration =
        match stream_audio_tracks::get_playlist_duration(&mut connection, &stream_id).await {
            Ok(0) => {
                error!("Stream tracks list has zero duration");

                return Ok(HttpResponse::Conflict().finish());
            }
            Ok(tracks_duration) => tracks_duration,
            Err(error) => {
                error!(?error, "Unable to count stream tracks duration");

                return Ok(HttpResponse::InternalServerError().finish());
            }
        };

    let time_offset = match stream.calculate_time_offset(&timestamp, &tracks_duration) {
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

    let tracks = match stream_audio_tracks::get_current_and_next_audio_tracks_at_offset(
        &mut connection,
        &stream_id,
        &time_offset,
    )
    .await
    {
        Ok(tracks) => tracks,
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    match tracks {
        Some((current_track, _)) => Ok(HttpResponse::Ok().json(serde_json::json!({
            "code": 1i32,
            "message": "OK",
            "data": StreamTracksEntryWithPosition {
                position: time_offset - (current_track.time_offset as i64),
                track_entry: current_track,
            },
        }))),
        None => Ok(HttpResponse::Conflict().finish()),
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
) -> Response {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let mut conn = mysql_client.connection().await?;

    let stream = match streams::get_public_stream(&mut conn, &stream_id).await {
        Ok(Some(stream)) => stream,
        Ok(None) => {
            return Ok(HttpResponse::NotFound().finish());
        }
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    let tracks_duration =
        match stream_audio_tracks::get_playlist_duration(&mut conn, &stream_id).await {
            Ok(0) => {
                error!("Stream tracks list has zero duration");

                return Ok(HttpResponse::Conflict().finish());
            }
            Ok(tracks_duration) => tracks_duration,
            Err(error) => {
                error!(?error, "Unable to count stream tracks duration");

                return Ok(HttpResponse::InternalServerError().finish());
            }
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

    let tracks = match stream_audio_tracks::get_current_and_next_audio_tracks_at_offset(
        &mut conn,
        &stream_id,
        &time_offset,
    )
    .await
    {
        Ok(tracks) => tracks,
        Err(error) => {
            error!(?error, "Unable to get stream information");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    };

    match tracks {
        Some((current_track, next_track)) => Ok(HttpResponse::Ok().json(serde_json::json!({
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
        }))),
        None => Ok(HttpResponse::Conflict().finish()),
    }
}
