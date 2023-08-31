use crate::config::Config;
use crate::data_structures::{SortingColumn, SortingOrder, StreamId, TrackId, UserId};
use crate::http_server::response::Response;
use crate::services::ffmpeg_service::{
    transcode_audio_file, AudioChannels, AudioCodec, AudioContainer, TranscodeAudioFileFormat,
};
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_user_streams_having_track,
};
use crate::storage::db::repositories::user_stream_tracks::{
    get_stream_tracks, GetUserStreamTracksParams,
};
use crate::storage::db::repositories::user_tracks::{
    delete_user_track, get_single_user_track, get_user_tracks, GetUserTracksParams,
};
use crate::storage::db::row_utils::GetFilePath;
use crate::storage::fs::utils::GetPath;
use crate::storage::fs::FileSystem;
use crate::utils::TeeResultUtils;
use crate::MySqlClient;
use actix_web::web::{Data, Form, Path, Query};
use actix_web::{web, HttpResponse};
use futures::{channel::mpsc, StreamExt};
use serde::Deserialize;
use std::time::Duration;
use tracing::error;

#[derive(Deserialize)]
pub(crate) struct GetUserAudioTracksQuery {
    #[serde(default)]
    color_id: Option<String>,
    #[serde(default)]
    filter: Option<String>,
    #[serde(default)]
    offset: u32,
    #[serde(default)]
    unused: bool,
    #[serde(default)]
    row: SortingColumn,
    #[serde(default)]
    order: SortingOrder,
}

pub(crate) async fn get_user_audio_tracks(
    user_id: UserId,
    query: web::Query<GetUserAudioTracksQuery>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    let mut conn = mysql_client.connection().await?;

    let track_rows = get_user_tracks(
        &mut conn,
        &user_id,
        &GetUserTracksParams {
            color: color_id,
            filter: params.filter,
            sorting_column: params.row,
            sorting_order: params.order,
            unused: params.unused,
        },
        &params.offset,
    )
    .await
    .tee_err(|error| {
        error!(?error, "Failed to get user audio tracks from repository");
    })?;

    let tracks_json: Vec<_> = track_rows
        .into_iter()
        .map(|row| {
            serde_json::json!({
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
            })
        })
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": tracks_json,
    })))
}

#[derive(Deserialize)]
pub(crate) struct GetUserPlaylistAudioTracksQuery {
    #[serde(default)]
    color_id: Option<String>,
    #[serde(default)]
    filter: Option<String>,
    #[serde(default)]
    offset: u32,
}

pub(crate) async fn get_user_stream_audio_tracks(
    path: web::Path<StreamId>,
    user_id: UserId,
    query: web::Query<GetUserPlaylistAudioTracksQuery>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let color_id = match params.color_id {
        None => None,
        Some(str) if str.is_empty() => None,
        Some(str) => str.parse::<u32>().ok(),
    };

    let mut connection = mysql_client.connection().await?;

    match get_single_stream_by_id(&mut connection, &stream_id).await {
        Ok(Some(stream)) if stream.uid == user_id => (),
        Ok(Some(_)) => return Ok(HttpResponse::Forbidden().finish()),
        Ok(None) => return Ok(HttpResponse::NotFound().finish()),
        Err(error) => {
            error!(?error, "Failed to get user stream");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    }

    let track_rows = get_stream_tracks(
        &mut connection,
        &stream_id,
        &GetUserStreamTracksParams {
            color: color_id,
            filter: params.filter,
        },
        &params.offset,
    )
    .await
    .tee_err(|error| {
        error!(
            ?error,
            "Failed to get user stream audio tracks from repository"
        );
    })?;

    let tracks_json: Vec<_> = track_rows
        .into_iter()
        .map(|row| {
            serde_json::json!({
                "t_order": row.link.t_order,
                "unique_id": row.link.unique_id,
                "time_offset": row.link.time_offset,
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
            })
        })
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "code": 1i32,
        "message": "OK",
        "data": tracks_json,
    })))
}

#[derive(Deserialize)]
pub(crate) struct UploadedFile {}

#[derive(Deserialize)]
pub(crate) struct UploadAudioTrackForm {
    #[serde(default)]
    stream_id: Option<StreamId>,
    #[serde(default)]
    up_next: bool,
    file: UploadedFile,
}

pub(crate) async fn upload_audio_track(
    user_id: UserId,
    form: Form<UploadAudioTrackForm>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn delete_audio_track<FS: FileSystem>(
    user_id: UserId,
    path: Path<TrackId>,
    mysql_client: Data<MySqlClient>,
    file_system: Data<FS>,
) -> Response {
    let track_id = path.into_inner();

    let mut connection = mysql_client.transaction().await?;

    let track_row = match get_single_user_track(&mut connection, &track_id)
        .await
        .tee_err(|error| error!(?error, "Unable to get user track from database"))?
    {
        Some(track_row) => track_row,
        None => return Ok(HttpResponse::NotFound().finish()),
    };

    if track_row.track.uid != user_id {
        return Ok(HttpResponse::Forbidden().finish());
    }

    let stream_rows = get_user_streams_having_track(&mut connection, &track_row.track.tid)
        .await
        .tee_err(|error| {
            error!(
                ?error,
                "Unable to get user streams having given track from database"
            )
        })?;

    for stream_row in stream_rows.iter() {
        todo!()
    }

    delete_user_track(&mut connection, &*track_row).await?;

    file_system
        .delete_file(&format!("audio/{}", track_row.file.get_path()))
        .await?;

    connection.commit().await?;

    Ok(HttpResponse::Ok().finish())
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct TranscodeAudioTrackQuery {
    #[serde(default)]
    #[serde(with = "serde_millis")]
    pub(crate) initial_position: Duration,
}

pub(crate) async fn transcode_audio_track(
    user_id: UserId,
    path: Path<TrackId>,
    mysql_client: Data<MySqlClient>,
    config: Data<Config>,
    json: Query<TranscodeAudioTrackQuery>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    let track_id = path.into_inner();
    let track_row = match get_single_user_track(&mut connection, &track_id)
        .await
        .tee_err(|error| error!(?error, "Unable to get user track from database"))?
    {
        Some(track_row) => track_row,
        None => return Ok(HttpResponse::NotFound().finish()),
    };

    if track_row.track.uid != user_id {
        return Ok(HttpResponse::Forbidden().finish());
    }

    let source_path = track_row.get_file_path();
    let source_url = format!("{}audio/{}", config.file_server_endpoint, source_path);

    let (response_tx, response_rx) = mpsc::channel(32);

    actix_rt::spawn({
        async move {
            if let Err(error) = transcode_audio_file(
                &source_url,
                response_tx,
                json.initial_position,
                TranscodeAudioFileFormat {
                    container: AudioContainer::Adts,
                    codec: AudioCodec::Aac,
                    channels: AudioChannels::Stereo,
                    bitrate: 256_000,
                    sampling_rate: 48_000,
                },
            )
            .await
            {
                error!(?error, "Error occurred while transcoding audio");
            }
        }
    });

    use futures::StreamExt;

    let mut response = HttpResponse::Ok();

    response.content_type("audio/aac").force_close();

    Ok(response.streaming::<_, actix_web::Error>(response_rx.map(Ok)))
}
