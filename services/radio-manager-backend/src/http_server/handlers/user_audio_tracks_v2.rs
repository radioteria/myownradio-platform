use crate::data_structures::{SortingColumn, SortingOrder, StreamId, UserId};
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::storage::db::repositories::{
    streams, user_stream_tracks, user_tracks, LinkRow, TrackRow,
};
use crate::utils::TeeResultUtils;
use actix_web::{web, HttpResponse};
use serde::Deserialize;
use tracing::error;

const MAX_TRACKS_PER_REQUEST: i64 = 500;

const DEFAULT_TRACKS_PER_REQUEST: i64 = 50;

fn serialize_track_row(track: &TrackRow) -> serde_json::Value {
    serde_json::json!({
        "album": track.album,
        "artist": track.artist,
        "date": track.date,
        "duration": track.duration,
        "filename": track.filename,
        "genre": track.genre,
        "tid": track.tid,
        "title": track.title,
        "trackNumber": track.track_number
    })
}

fn serialize_link_row(link: &LinkRow) -> serde_json::Value {
    serde_json::json!({
        "uniqueId": link.unique_id
    })
}

#[derive(Deserialize)]
pub(crate) struct GetUserAudioTracksQuery {
    filter: Option<String>,
    #[serde(default)]
    offset: i64,
    #[serde(default)]
    limit: Option<i64>,
}

pub(crate) async fn get_user_audio_tracks(
    user_id: UserId,
    query: web::Query<GetUserAudioTracksQuery>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let params = query.into_inner();

    let mut conn = mysql_client.connection().await?;

    let offset = params.offset;
    let limit = params
        .limit
        .unwrap_or(DEFAULT_TRACKS_PER_REQUEST)
        .min(MAX_TRACKS_PER_REQUEST);

    let tracks_count = user_tracks::get_user_tracks_count(
        &mut conn,
        &user_id,
        &user_tracks::GetUserTracksTotalParams {
            color: None,
            filter: params.filter.clone(),
            unused: false,
        },
    )
    .await
    .tee_err(|error| {
        error!(
            ?error,
            "Failed to get user audio tracks total from repository"
        );
    })?;

    let track_items = user_tracks::get_user_tracks(
        &mut conn,
        &user_id,
        &user_tracks::GetUserTracksParams {
            color: None,
            filter: params.filter,
            sorting_column: SortingColumn::TrackId,
            sorting_order: SortingOrder::Desc,
            unused: false,
        },
        &Some(offset),
        &Some(limit),
    )
    .await
    .tee_err(|error| {
        error!(?error, "Failed to get user audio tracks from repository");
    })?;

    let track_items_json: Vec<_> = track_items
        .into_iter()
        .map(|row| serialize_track_row(&row.track))
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "totalCount": tracks_count,
        "items": track_items_json,
        "paginationData": {
            "limit": limit,
            "offset": offset
        }
    })))
}

pub(crate) async fn get_unused_user_audio_tracks(
    user_id: UserId,
    query: web::Query<GetUserAudioTracksQuery>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let params = query.into_inner();

    let mut conn = mysql_client.connection().await?;

    let offset = params.offset;
    let limit = params
        .limit
        .unwrap_or(DEFAULT_TRACKS_PER_REQUEST)
        .min(MAX_TRACKS_PER_REQUEST);

    let tracks_count = user_tracks::get_user_tracks_count(
        &mut conn,
        &user_id,
        &user_tracks::GetUserTracksTotalParams {
            color: None,
            filter: params.filter.clone(),
            unused: true,
        },
    )
    .await
    .tee_err(|error| {
        error!(
            ?error,
            "Failed to get user audio tracks total from repository"
        );
    })?;

    let track_items = user_tracks::get_user_tracks(
        &mut conn,
        &user_id,
        &user_tracks::GetUserTracksParams {
            color: None,
            filter: params.filter,
            sorting_column: SortingColumn::TrackId,
            sorting_order: SortingOrder::Desc,
            unused: false,
        },
        &Some(offset),
        &Some(limit),
    )
    .await
    .tee_err(|error| {
        error!(?error, "Failed to get user audio tracks from repository");
    })?;

    let track_items_json: Vec<_> = track_items
        .into_iter()
        .map(|row| serialize_track_row(&row.track))
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "totalCount": tracks_count,
        "items": track_items_json,
        "paginationData": {
            "limit": limit,
            "offset": offset
        }
    })))
}

#[derive(Deserialize)]
pub(crate) struct GetChannelAudioTracksQuery {
    #[serde(default)]
    filter: Option<String>,
    #[serde(default)]
    offset: i64,
    #[serde(default)]
    limit: Option<i64>,
}

pub(crate) async fn get_channel_audio_tracks(
    path: web::Path<StreamId>,
    user_id: UserId,
    query: web::Query<GetChannelAudioTracksQuery>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    let stream_id = path.into_inner();
    let params = query.into_inner();

    let offset = params.offset;
    let limit = params
        .limit
        .unwrap_or(DEFAULT_TRACKS_PER_REQUEST)
        .min(DEFAULT_TRACKS_PER_REQUEST);

    let mut connection = mysql_client.connection().await?;

    match streams::get_single_stream_by_id(&mut connection, &stream_id).await {
        Ok(Some(stream)) if stream.uid == user_id => (),
        Ok(Some(_)) => return Ok(HttpResponse::Forbidden().finish()),
        Ok(None) => return Ok(HttpResponse::NotFound().finish()),
        Err(error) => {
            error!(?error, "Failed to get user stream");

            return Ok(HttpResponse::InternalServerError().finish());
        }
    }

    let total_count = user_stream_tracks::get_stream_tracks_count(
        &mut connection,
        &stream_id,
        &user_stream_tracks::GetUserStreamTracksParams {
            color: None,
            filter: params.filter.clone(),
        },
    )
    .await
    .tee_err(|error| {
        error!(
            ?error,
            "Failed to get user stream audio tracks from repository"
        );
    })?;

    let track_items = user_stream_tracks::get_stream_tracks(
        &mut connection,
        &stream_id,
        &user_stream_tracks::GetUserStreamTracksParams {
            color: None,
            filter: params.filter,
        },
        &Some(offset),
        &Some(limit),
    )
    .await
    .tee_err(|error| {
        error!(
            ?error,
            "Failed to get user stream audio tracks from repository"
        );
    })?;

    let track_items_json: Vec<_> = track_items
        .into_iter()
        .map(|row| {
            serde_json::json!({
                "track": serialize_track_row(&row.track),
                "entry": serialize_link_row(&row.link),
            })
        })
        .collect();

    Ok(HttpResponse::Ok().json(serde_json::json!({
        "totalCount": total_count,
        "items": track_items_json,
        "paginationData": {
            "limit": limit,
            "offset": offset
        }
    })))
}
