use crate::data_structures::StreamId;
use crate::http_server::response::Response;
use crate::storage::db::repositories::streams;
use crate::storage::db::repositories::user_stream_tracks::TrackFileLinkMergedRow;
use crate::{services, Config, MySqlClient, StreamServiceFactory};
use actix_web::{web, HttpResponse};
use std::time::UNIX_EPOCH;

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

pub(crate) async fn get_playing_at(
    path: web::Path<(StreamId, u64)>,
    config: web::Data<Config>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let (stream_id, unix_time) = path.into_inner();
    let system_time = UNIX_EPOCH + std::time::Duration::from_millis(unix_time);

    let mut connection = mysql_client.connection().await?;

    let stream = {
        match streams::get_single_stream_by_id(&mut connection, &stream_id).await? {
            Some(stream) => stream,
            None => return Ok(HttpResponse::NotFound().finish()),
        }
    };

    let (current_track, next_track, current_position, status) = {
        match services::get_now_playing(&system_time, &stream_id, &mut connection).await? {
            Some(now_playing) => now_playing,
            None => return Ok(HttpResponse::Conflict().finish()),
        }
    };

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": {
            "playlist_position": current_track.link.t_order,
            "playback_status": status,
            "current_track": {
                "offset": current_position.num_milliseconds(),
                "title": get_artist_and_title(&current_track),
                "url": format!("{}audio/{}", config.file_server_endpoint, get_file_path(&current_track)),
                "duration": current_track.track.duration,
            },
            "next_track": {
                "title": get_artist_and_title(&next_track),
                "url": format!("{}audio/{}", config.file_server_endpoint, get_file_path(&next_track)),
                "duration": next_track.track.duration,
            },
        },
    })))
}

pub(crate) async fn skip_track(
    params: web::Path<StreamId>,
    stream_service_factory: web::Data<StreamServiceFactory>,
) -> Response {
    let stream_id = params.into_inner();
    let stream_service = stream_service_factory.create_service(&stream_id).await?;

    stream_service.play_next().await?;

    Ok(HttpResponse::Ok().finish())
}
