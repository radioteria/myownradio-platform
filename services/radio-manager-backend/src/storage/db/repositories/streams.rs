use crate::data_structures::{StreamId, TrackId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::{RepositoryError, RepositoryResult};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use chrono::Duration;
use sqlx::{query, Execute, MySql, QueryBuilder, Row};
use std::ops::{Deref, DerefMut};
use tracing::trace;

fn create_select_query_builder<'a>() -> QueryBuilder<'a, MySql> {
    QueryBuilder::new(
        r#"
SELECT `r_streams`.`sid`,
       `r_streams`.`uid`,
       `r_streams`.`name`,
       `r_streams`.`permalink`,
       `r_streams`.`info`,
       `r_streams`.`jingle_interval`,
       `r_streams`.`status`,
       `r_streams`.`started`,
       `r_streams`.`started_from`,
       `r_streams`.`access`,
       `r_streams`.`category`,
       `r_streams`.`hashtags`,
       `r_streams`.`cover`,
       `r_streams`.`cover_background`,
       `r_streams`.`created`,
       `r_streams`.`rtmp_url`,
       `r_streams`.`rtmp_streaming_key`
FROM `r_streams`
"#,
    )
}

pub(crate) async fn get_stream_playlist_duration(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
) -> RepositoryResult<Duration> {
    let sql = r#"
SELECT CAST(SUM(`r_tracks`.`duration`) AS SIGNED) as `sum`
FROM `r_tracks` 
JOIN `r_link` ON `r_tracks`.`tid` = `r_link`.`track_id`
WHERE `r_link`.`stream_id` = ?
    "#;

    trace!("Running SQL query: {}", sql);

    let duration = query(sql)
        .bind(stream_id.deref())
        .fetch_one(connection.deref_mut())
        .await
        .map(|row| row.get::<Option<i64>, _>("sum"))?
        .unwrap_or_default();

    Ok(Duration::milliseconds(duration))
}

pub(crate) async fn get_single_stream_by_id(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
) -> RepositoryResult<Option<StreamRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_streams`.`sid` = ");
    builder.push_bind(stream_id.deref());
    builder.push(" OR `r_streams`.`permalink` = ");
    builder.push_bind(stream_id.deref());
    builder.push(" LIMIT 1");

    let query = builder.build_query_as();

    trace!("Running SQL query: {}", query.sql());

    let stream = query.fetch_optional(connection.deref_mut()).await?;

    Ok(stream)
}

pub(crate) async fn get_user_streams_by_user_id(
    connection: &mut MySqlConnection,
    user_id: &UserId,
) -> RepositoryResult<Vec<StreamRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_streams`.`uid` = ");
    builder.push_bind(user_id.deref());

    let query = builder.build_query_as();

    trace!("Running SQL query: {}", query.sql());

    let stream = query.fetch_all(connection.deref_mut()).await?;

    Ok(stream)
}

pub(crate) async fn get_user_streams_having_track(
    connection: &mut MySqlConnection,
    track_id: &TrackId,
) -> RepositoryResult<Vec<StreamRow>> {
    let mut builder = create_select_query_builder();

    builder.push(
        r#" WHERE (
SELECT COUNT(`id`) 
FROM `r_links` 
WHERE `r_links`.`stream_id` = `r_streams`.`sid` 
  AND `r_links`.`track_id` = "#,
    );
    builder.push_bind(track_id.deref());
    builder.push(") > 0");

    let query = builder.build_query_as();

    trace!("Running SQL query: {}", query.sql());

    let stream = query.fetch_all(connection.deref_mut()).await?;

    Ok(stream)
}

pub(crate) async fn update_stream_status(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
    stream_status: &StreamStatus,
    started_at: &Option<i64>,
    started_from: &Option<i64>,
) -> RepositoryResult<()> {
    query("UPDATE `r_streams` SET `status` = ?, `started` = ?, `started_from` = ? WHERE `sid` = ?")
        .bind(stream_status.clone())
        .bind(started_at.clone())
        .bind(started_from.clone())
        .bind(stream_id.deref())
        .execute(connection.deref_mut())
        .await?;

    Ok(())
}

pub(crate) async fn seek_user_stream_forward(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
    seek_time: &Duration,
) -> RepositoryResult<()> {
    query(
        r#"
UPDATE `r_streams`
SET `started_from` = `started_from` + ?
WHERE `sid` = ?
  AND `started_from` IS NOT NULL
"#,
    )
    .bind(seek_time.num_milliseconds())
    .bind(stream_id.deref())
    .execute(connection.deref_mut())
    .await?;

    Ok(())
}

pub(crate) async fn update_channel_rtmp_parameters(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
    user_id: &UserId,
    rtmp_url: &str,
    rtmp_streaming_key: &str,
) -> RepositoryResult<()> {
    query(
        r#"
UPDATE `r_streams` 
SET `rtmp_url` = ?, `rtmp_streaming_key` = ?
WHERE `sid` = ? AND `uid` = ?
"#,
    )
    .bind(rtmp_url)
    .bind(rtmp_streaming_key)
    .bind(stream_id)
    .bind(user_id)
    .execute(connection.deref_mut())
    .await?;

    Ok(())
}
