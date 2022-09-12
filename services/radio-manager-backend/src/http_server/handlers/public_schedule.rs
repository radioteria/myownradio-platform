use crate::http_server::response::Response;
use crate::models::audio_track::StreamTracksEntry;
use crate::models::types::StreamId;
use crate::repositories::{stream_audio_tracks, streams};
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_stream_playlist_duration,
};
use crate::storage::db::repositories::user_stream_tracks::{
    get_current_and_next_stream_track_at_time_offset, get_single_stream_track_at_time_offset,
    TrackFileLinkMergedRow,
};
use crate::storage::db::repositories::StreamStatus;
use crate::utils::TeeResultUtils;
use crate::{Config, MySqlClient};
use actix_web::middleware::Logger;
use actix_web::{web, HttpResponse, Responder};
use serde::{Deserialize, Serialize};
use std::time::{Duration, SystemTime, UNIX_EPOCH};
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

    let stream = match get_single_stream_by_id(&mut connection, &stream_id)
        .await
        .tee_err(|error| error!("Unable to get stream information"))?
    {
        Some(stream) => stream,
        None => return Ok(HttpResponse::NotFound().finish()),
    };

    let playlist_duration = get_stream_playlist_duration(&mut connection, &stream_id)
        .await
        .tee_err(|error| error!("Unable to get stream playlist duration"))?;

    if let (StreamStatus::Playing, Some(started_at), Some(started_from)) =
        (&stream.status, &stream.started, &stream.started_from)
    {
        let time_offset = Duration::from_millis(
            (((timestamp - started_at) + started_from) % playlist_duration.as_millis() as i64)
                as u64,
        );
        if let Some((row, track_position)) =
            get_single_stream_track_at_time_offset(&mut connection, &stream_id, &time_offset)
                .await
                .tee_err(|error| error!("Unable to get stream playlist track"))?
        {
            return Ok(HttpResponse::Ok().json(serde_json::json!({
                "code": 1i32,
                "message": "OK",
                "data": {
                    "position": track_position.as_millis() as i64,
                    "time_offset": row.link.time_offset,
                    "t_order": row.link.t_order,
                    "unique_id": row.link.unique_id,
                    "album": row.track.album,
                    "artist": row.track.artist,
                    "buy": row.track.buy,
                    "can_be_shared": row.track.can_be_shared,
                    "color": row.track.color,
                    "cue": row.track.cue,
                    "date": row.track.date,
                    "duration": row.track.duration,
                    "filename": row.track.filename,
                    "genre": row.track.genre,
                    "is_new": row.track.is_new,
                    "tid": row.track.tid,
                    "title": row.track.title,
                    "track_number": row.track.track_number
                },
            })));
        }
    };

    Ok(HttpResponse::Conflict().finish())
}

fn get_artist_and_title(row: &TrackFileLinkMergedRow) -> String {
    format!("{} - {}", row.track.artist, row.track.title)
}

fn get_file_path(row: &TrackFileLinkMergedRow) -> String {
    format!(
        "{}/{}/{}.{}",
        &row.file.file_hash[..1],
        &row.file.file_hash[1..2],
        row.file.file_hash,
        row.file.file_extension
    )
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

    let mut connection = mysql_client.connection().await?;

    let stream = match get_single_stream_by_id(&mut connection, &stream_id)
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
        .tee_err(|error| error!(?error, "Unable to get stream playlist duration"))?;

    if let (StreamStatus::Playing, Some(started_at), Some(started_from)) =
        (&stream.status, &stream.started, &stream.started_from)
    {
        let time_offset = Duration::from_millis(
            (((params.timestamp - started_at) + started_from)
                % playlist_duration.as_millis() as i64) as u64,
        );
        if let Some((current, next, track_time_pos)) =
            get_current_and_next_stream_track_at_time_offset(
                &mut connection,
                &stream_id,
                &time_offset,
            )
            .await
            .tee_err(|error| error!(?error, "Unable to get stream playlist tracks"))?
        {
            return Ok(HttpResponse::Ok().json(serde_json::json!({
                "code": 1i32,
                "message": "OK",
                "data": {
                    "time": params.timestamp,
                    "playlist_position": current.link.t_order,
                    "current_track": {
                        "offset": track_time_pos.as_millis() as i64,
                        "title": get_artist_and_title(&current),
                        "url": format!("{}audio/{}", config.file_server_endpoint, get_file_path(&current)),
                        "duration": current.track.duration,
                    },
                    "next_track": {
                        "title": get_artist_and_title(&next),
                        "url": format!("{}audio/{}", config.file_server_endpoint, get_file_path(&next)),
                        "duration": next.track.duration,
                    },
                },
            })));
        }
    }

    Ok(HttpResponse::Conflict().finish())
}
