use crate::models::stream::Stream;
use crate::models::types::{StreamId, UserId};
use crate::mysql_client::MySqlConnection;
use sqlx::{query, Error, Execute, MySql, QueryBuilder};
use std::ops::{Deref, DerefMut};
use tracing::trace;

pub(crate) async fn get_public_stream(
    conn: &mut MySqlConnection,
    stream_id: &StreamId,
) -> Result<Option<Stream>, Error> {
    let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

    builder.push(" WHERE sid = ");
    builder.push_bind(stream_id.deref());

    builder.push(" OR permalink = ");
    builder.push_bind(stream_id.deref());

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let stream = query
        .fetch_optional(conn.deref_mut())
        .await
        .map(|row| row.map(|ref row| row.into()))?;

    Ok(stream)
}

pub(crate) async fn get_single_user_stream(
    conn: &mut MySqlConnection,
    user_id: &UserId,
    stream_id: &StreamId,
) -> Result<Option<Stream>, Error> {
    let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

    builder.push(" WHERE uid = ");
    builder.push_bind(user_id.deref());

    builder.push(" AND sid = ");
    builder.push_bind(stream_id.deref());

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let streams = query
        .fetch_optional(conn.deref_mut())
        .await
        .map(|row| row.map(|ref row| row.into()))?;

    Ok(streams)
}

pub(crate) async fn get_user_streams(
    conn: &mut MySqlConnection,
    user_id: &UserId,
) -> Result<Vec<Stream>, Error> {
    let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

    builder.push(" WHERE uid = ");
    builder.push_bind(user_id.deref());

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let streams = query
        .fetch_all(conn.deref_mut())
        .await
        .map(|rows| rows.iter().map(Into::into).collect())?;

    Ok(streams)
}

pub(crate) async fn seek_forward_user_stream(
    conn: &mut MySqlConnection,
    stream_id: &StreamId,
    seek_time: i64,
) -> Result<(), Error> {
    query("UPDATE `r_streams` SET `started_from` = `started_from` - ? WHERE `sid` = ? AND `started_from` IS NOT NULL")
        .bind(seek_time).bind(stream_id)
        .fetch_all(conn.deref_mut())
        .await?;

    Ok(())
}

pub(crate) async fn seek_backward_user_stream(
    conn: &mut MySqlConnection,
    stream_id: &StreamId,
    seek_time: i64,
) -> Result<(), Error> {
    query("UPDATE `r_streams` SET `started_from` = `started_from` + ? WHERE `sid` = ? AND `started_from` IS NOT NULL")
        .bind(seek_time)
        .bind(stream_id)
        .fetch_all(conn.deref_mut())
        .await?;

    Ok(())
}
