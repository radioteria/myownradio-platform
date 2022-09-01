use crate::models::audio_track::StreamTracksEntry;
use crate::models::types::{StreamId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::repositories::DEFAULT_TRACKS_PER_REQUEST;
use sqlx::{
    query, query_as, Acquire, Database, Error, Execute, MySql, MySqlExecutor, QueryBuilder, Row,
    Type,
};
use std::ops::{Deref, DerefMut};
use tracing::trace;

pub(crate) async fn get_playlist_duration(
    mut conn: &mut MySqlConnection,
    stream_id: &StreamId,
) -> Result<i64, Error> {
    let sql = r#"
SELECT CAST(SUM(`r_tracks`.`duration`) AS SIGNED) as `sum`
FROM `r_tracks` 
JOIN `r_link` ON `r_tracks`.`tid` = `r_link`.`track_id`
WHERE `r_link`.`stream_id` = ?
    "#;

    let tracks_duration = query(sql)
        .bind(stream_id.deref())
        .fetch_one(conn.deref_mut())
        .await
        .map(|row| row.get::<Option<i64>, _>("sum"))?;

    Ok(tracks_duration.unwrap_or_default())
}

fn create_stream_audio_tracks_builder<'a>() -> QueryBuilder<'a, MySql> {
    QueryBuilder::new(
        r#"
SELECT `r_tracks`.`tid`,
       `r_tracks`.`file_id`,
       `r_tracks`.`uid`,
       `r_tracks`.`filename`,
       `r_tracks`.`hash`,
       `r_tracks`.`ext`,
       `r_tracks`.`artist`,
       `r_tracks`.`title`,
       `r_tracks`.`album`,
       `r_tracks`.`track_number`,
       `r_tracks`.`genre`,
       `r_tracks`.`date`,
       `r_tracks`.`cue`,
       `r_tracks`.`buy`,
       `r_tracks`.`duration`,
       `r_tracks`.`filesize`,
       `r_tracks`.`color`,
       `r_tracks`.`uploaded`,
       `r_tracks`.`copy_of`,
       `r_tracks`.`used_count`,
       `r_tracks`.`is_new`,
       `r_tracks`.`can_be_shared`,
       `r_tracks`.`is_deleted`,
       `r_tracks`.`deleted`,
       `fs_file`.`file_hash`,
       `fs_file`.`file_size`,
       `fs_file`.`file_extension`,
       `r_link`.`id`,
       `r_link`.`stream_id`,
       `r_link`.`track_id`,
       `r_link`.`t_order`,
       `r_link`.`unique_id`,
       `r_link`.`time_offset`
FROM `r_tracks` 
JOIN `fs_file` ON `fs_file`.`file_id` = `r_tracks`.`file_id`
JOIN `r_link` ON `r_tracks`.`tid` = `r_link`.`track_id`
"#,
    )
}

pub(crate) async fn get_audio_track_at_offset(
    mut conn: &mut MySqlConnection,
    stream_id: &StreamId,
    time_offset: &i64,
) -> Result<Option<StreamTracksEntry>, Error> {
    let tracks_duration = match get_playlist_duration(conn, stream_id).await? {
        0 => return Ok(None),
        duration => duration,
    };

    let mut builder = create_stream_audio_tracks_builder();

    builder.push(" WHERE `r_link`.`stream_id` = ");
    builder.push_bind(stream_id.deref());
    builder.push(" AND `r_link`.`time_offset` + `r_tracks`.`duration` >= ");
    builder.push_bind(time_offset % tracks_duration);
    builder.push(" ORDER BY `r_link`.`t_order` LIMIT 1");

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    query
        .fetch_optional(conn.deref_mut())
        .await
        .map(|row| row.as_ref().map(Into::into))
}

pub(crate) async fn get_current_and_next_audio_tracks_at_offset(
    mut conn: &mut MySqlConnection,
    stream_id: &StreamId,
    time_offset: &i64,
) -> Result<Option<(StreamTracksEntry, StreamTracksEntry)>, Error> {
    let mut builder = create_stream_audio_tracks_builder();

    builder.push(" WHERE `r_link`.`stream_id` = ");
    builder.push_bind(stream_id.deref());
    builder.push(" AND `r_link`.`time_offset` + `r_tracks`.`duration` >= ");
    builder.push_bind(time_offset);

    builder.push(" ORDER BY `r_link`.`t_order` LIMIT 2");

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let audio_tracks: Vec<StreamTracksEntry> = query
        .fetch_all(conn.deref_mut())
        .await
        .map(|rows| rows.iter().map(Into::into).collect())?;

    match audio_tracks.len() {
        0 => return Ok(None),
        1 => {
            // In case if it's the last track in tracklist, next track will be the first in tracklist
            let mut builder = create_stream_audio_tracks_builder();
            builder.push(" WHERE `r_link`.`stream_id` = ");
            builder.push_bind(stream_id.deref());
            builder.push(" ORDER BY `r_link`.`t_order` LIMIT 1");
            let query = builder.build();

            trace!("Running SQL query: {}", query.sql());

            let audio_track = query
                .fetch_one(conn.deref_mut())
                .await
                .map(|ref row| row.into())?;

            Ok(Some((audio_tracks[0].clone(), audio_track)))
        }
        _ => Ok(Some((audio_tracks[0].clone(), audio_tracks[1].clone()))),
    }
}

pub(crate) async fn get_user_stream_audio_tracks(
    mut conn: &mut MySqlConnection,
    user_id: &UserId,
    stream_id: &StreamId,
    color: &Option<u32>,
    filter: &Option<String>,
    offset: &u32,
) -> Result<Vec<StreamTracksEntry>, Error> {
    let mut builder = create_stream_audio_tracks_builder();

    builder.push(" WHERE `r_link`.`stream_id` = ");
    builder.push_bind(stream_id.deref());
    builder.push(" AND `r_tracks`.`uid` = ");
    builder.push_bind(user_id.deref());

    if let Some(filter) = filter {
        if !filter.is_empty() {
            builder.push(" AND MATCH(artist, title, genre) AGAINST (");
            builder.push_bind(filter);
            builder.push(" IN BOOLEAN MODE)");
        }
    };

    if let Some(color) = color {
        builder.push(" AND color = ");
        builder.push_bind(color);
    };

    builder.push(" ORDER BY `r_link`.`t_order`");

    builder.push(" LIMIT ");
    builder.push_bind(offset);
    builder.push(", ");
    builder.push_bind(DEFAULT_TRACKS_PER_REQUEST);

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let audio_tracks = query
        .fetch_all(conn.deref_mut())
        .await
        .map(|rows| rows.iter().map(Into::into).collect())?;

    Ok(audio_tracks)
}
