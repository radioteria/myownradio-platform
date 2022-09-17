use crate::data_structures::{StreamId, TrackId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::user_stream_tracks::TrackFileLinkMergedRow;
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
    track: &TrackFileLinkMergedRow,
    stream: &StreamRow,
) -> TaskResult {
    let stream_id = &stream.sid;
    let track_id = &track.track.tid;

    if matches!(stream.status, StreamStatus::Playing) {
        let playlist_duration =
            streams::get_stream_playlist_duration(&mut transaction, &stream_id).await?;

        let time_offset = get_user_stream_playing_time(stream).unwrap_or_default();

        let now_playing = user_stream_tracks::get_single_stream_track_at_time_offset(
            &mut connection,
            stream_id,
            &(time_offset % playlist_duration),
        )
        .await?;

        if let Some((now_playing_track, time_position)) = now_playing {
            let mut laps_played = (time_offset.as_millis() / playlist_duration.as_millis()) as i64;
            if now_playing_track.link.t_order > track.link.t_order {
                laps_played += 1;
            }

            let mut seek_amount = laps_played * track.track.duration;
            if &now_playing_track.track.tid == track_id {
                seek_amount -= time_position.as_millis() as i64;
            }

            todo!()
        }
    }

    user_stream_tracks::delete_track_from_user_stream(&mut connection, track_id, stream_id).await?;

    user_stream_tracks::optimize_tracks_in_user_stream(&mut connection, stream_id).await?;

    Ok(())
}
