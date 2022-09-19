use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::user_tracks::TrackFileMergedRow;
use crate::storage::db::repositories::{streams, user_stream_tracks};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use crate::tasks::{TaskError, TaskResult};
use chrono::Duration;
use std::time::{SystemTime, UNIX_EPOCH};

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

        return Some(Duration::milliseconds((now - started_at) + started_from));
    };

    None
}

pub(crate) async fn delete_track_from_user_stream(
    mut connection: &mut MySqlConnection,
    track: &TrackFileMergedRow,
    stream: &StreamRow,
) -> TaskResult {
    let stream_id = &stream.sid;
    let track_id = &track.track.tid;

    if matches!(stream.status, StreamStatus::Playing) {
        let playlist_duration =
            streams::get_stream_playlist_duration(&mut connection, &stream_id).await?;

        let time_offset = get_user_stream_playing_time(stream).unwrap_or_else(|| Duration::zero());

        let now_playing = user_stream_tracks::get_single_stream_track_at_time_offset(
            &mut connection,
            stream_id,
            &Duration::milliseconds(
                time_offset.num_milliseconds() % playlist_duration.num_milliseconds(),
            ),
        )
        .await?;

        if let Some((now_playing_track, time_position)) = now_playing {
            let mut laps_played =
                time_offset.num_milliseconds() / playlist_duration.num_milliseconds();
            if now_playing_track.link.t_order > track.link.t_order {
                laps_played += 1;
            }

            let mut seek_amount = laps_played * track.track.duration;
            if &now_playing_track.track.tid == track_id {
                seek_amount -= time_position.num_milliseconds();
            }

            seek_amount = seek_amount.max(0);

            seek_user_stream(
                &mut connection,
                stream,
                &Duration::milliseconds(seek_amount),
            )
            .await?;
        }
    }

    user_stream_tracks::delete_track_from_user_stream(&mut connection, track_id, stream_id).await?;

    user_stream_tracks::optimize_tracks_in_user_stream(&mut connection, stream_id).await?;

    Ok(())
}

pub(crate) async fn seek_user_stream(
    mut connection: &mut MySqlConnection,
    stream: &StreamRow,
    seek_time: &Duration,
) -> TaskResult {
    if !matches!(stream.status, StreamStatus::Playing) {
        return Err(TaskError::StreamIsNotPlaying);
    }

    streams::seek_user_stream_forward(&mut connection, &stream.sid, &seek_time).await?;

    Ok(())
}
