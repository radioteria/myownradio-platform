use crate::models::types::StreamId;
use crate::mysql_client::MySqlConnection;
use sqlx::{query, Error, Row};
use std::ops::{Deref, DerefMut};

pub(crate) async fn get_playlist_duration(
    mut conn: &mut MySqlConnection,
    stream_id: &StreamId,
) -> Result<i64, Error> {
    let sql = r#"
        SELECT CAST(SUM(`r_tracks`.`duration`) AS SIGNED) as `sum`
        FROM `r_tracks` JOIN `r_link` ON `r_tracks`.`tid` = `r_link`.`track_id`
        WHERE `r_link`.`stream_id` = ?
    "#;

    let tracks_duration = query(sql)
        .bind(stream_id.deref())
        .fetch_one(conn.deref_mut())
        .await
        .map(|row| row.get::<Option<i64>, _>("sum"))?;

    Ok(tracks_duration.unwrap_or_default())
}
