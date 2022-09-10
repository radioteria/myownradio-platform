use crate::models::types::StreamId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use sqlx::{query, Row};
use std::ops::{Deref, DerefMut};
use std::time::Duration;

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

    let duration = query(sql)
        .bind(stream_id.deref())
        .fetch_one(connection.deref_mut())
        .await
        .map(|row| row.get::<Option<i64>, _>("sum"))?
        .unwrap_or_default();

    Ok(Duration::from_millis(duration as u64))
}
