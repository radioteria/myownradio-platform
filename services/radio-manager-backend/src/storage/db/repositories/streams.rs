use crate::models::types::StreamId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::StreamRow;
use sqlx::{query, query_as, Row};
use std::ops::{Deref, DerefMut};
use std::time::Duration;
use tracing::trace;

pub(crate) async fn get_stream_playlist_duration(
    mut connection: &mut MySqlConnection,
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

    Ok(Duration::from_millis(duration as u64))
}

pub(crate) async fn get_single_stream_by_id(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
) -> RepositoryResult<Option<StreamRow>> {
    let sql = r#"
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
       `r_streams`.`created`
FROM `r_streams`
WHERE `r_streams`.`sid` = ?
LIMIT 1
    "#;

    trace!("Running SQL query: {}", sql);

    let stream = query_as(sql)
        .bind(stream_id.deref())
        .fetch_optional(connection.deref_mut())
        .await?;

    Ok(stream)
}
