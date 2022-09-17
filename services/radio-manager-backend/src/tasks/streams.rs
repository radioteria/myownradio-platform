use crate::data_structures::{StreamId, TrackId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::{streams, user_stream_tracks};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use crate::tasks::TaskResult;
use sqlx::query;
use std::ops::Deref;
use std::time::{Duration, SystemTime, UNIX_EPOCH};

pub(crate) fn get_user_stream_playing_time(user_stream: &StreamRow) -> Option<Duration> {
    if let (StreamStatus::Playing, Some(started_at), Some(started_from)) = (
        &user_stream.status,
        &user_stream.started,
        &user_stream.started_from,
    ) {
        let now = SystemTime::now()
            .duration_since(UNIX_EPOCH)
            .unwrap_or_default()
            .as_millis() as i64;

        Some(Duration::from_millis(
            ((now - started_at) + started_from) as u64,
        ))
    }

    None
}

pub(crate) async fn delete_track_from_user_stream(
    mut connection: &mut MySqlConnection,
    track_id: &TrackId,
    user_stream: &StreamRow,
) -> TaskResult {
    let stream_id = &user_stream.sid;

    if matches!(user_stream.status, StreamStatus::Playing) {
        let playlist_duration =
            streams::get_stream_playlist_duration(&mut transaction, &stream_id).await?;

        let time_offset = get_user_stream_playing_time(user_stream).unwrap_or_default();

        let now_playing = user_stream_tracks::get_single_stream_track_at_time_offset(
            &mut connection,
            stream_id,
            &(time_offset % playlist_duration),
        )
        .await?;

        if let Some((track, time_position)) = now_playing {
            if &track.track.tid == track_id {
                // Restart from next track
                todo!()
            }

            // Compensate position
            todo!()
        }
    }

    user_stream_tracks::delete_track_from_user_stream(&mut connection, track_id, stream_id).await?;

    user_stream_tracks::optimize_tracks_in_user_stream(&mut connection, stream_id).await?;

    Ok(())
}
