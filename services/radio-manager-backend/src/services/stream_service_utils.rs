use crate::data_structures::StreamId;
use crate::mysql_client::MySqlConnection;
use crate::services::StreamServiceError;
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_stream_playlist_duration,
};
use crate::storage::db::repositories::user_stream_tracks::{
    get_current_and_next_stream_track_at_time_offset, TrackFileLinkMergedRow,
};
use crate::storage::db::repositories::StreamStatus;
use crate::utils::positive_mod;
use chrono::Duration;
use std::time::{SystemTime, UNIX_EPOCH};

pub(crate) async fn get_now_playing(
    time: SystemTime,
    stream_id: &StreamId,
    mut connection: &mut MySqlConnection,
) -> Result<Option<(TrackFileLinkMergedRow, TrackFileLinkMergedRow, Duration)>, StreamServiceError>
{
    let stream_row = match get_single_stream_by_id(&mut connection, &stream_id).await? {
        Some(stream_row) => stream_row,
        None => return Err(StreamServiceError::StreamNotFound),
    };

    Ok(
        match (
            &stream_row.status,
            &stream_row.started,
            &stream_row.started_from,
        ) {
            (StreamStatus::Playing, Some(started_at), Some(started_from)) => {
                let time_millis = time.duration_since(UNIX_EPOCH).unwrap().as_millis() as i64;

                let stream_time_position = time_millis - started_at + started_from;
                let playlist_duration =
                    get_stream_playlist_duration(&mut connection, &stream_id).await?;
                let playlist_time_position =
                    positive_mod(stream_time_position, playlist_duration.num_milliseconds());

                get_current_and_next_stream_track_at_time_offset(
                    &mut connection,
                    &stream_id,
                    &Duration::milliseconds(playlist_time_position),
                )
                .await?
            }
            _ => None,
        },
    )
}
