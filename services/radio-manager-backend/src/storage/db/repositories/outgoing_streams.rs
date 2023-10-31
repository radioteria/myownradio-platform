use crate::data_structures::{StreamId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::{StreamDestination, StreamDestinationRow};
use chrono::Utc;
use sqlx::{query, Execute, MySql, QueryBuilder};
use std::ops::DerefMut;
use tracing::trace;

fn create_select_query_builder<'a>() -> QueryBuilder<'a, MySql> {
    QueryBuilder::new(
        r#"
SELECT `id`, 
       `user_id`,
       `channel_id`,
       `stream_id`,
       `duration`,
       `byte_count`,
       `created_at`,
       `updated_at`
FROM `outgoing_streams`
"#,
    )
}

pub(crate) async fn create_or_update_outging_stream(
    connection: &mut MySqlConnection,
    user_id: &UserId,
    channel_id: &StreamId,
    stream_id: &str,
    duration: &i64,
    byte_count: &i64,
) -> RepositoryResult<()> {
    let query = query(
        r#"
        INSERT INTO `outgoing_streams` (`user_id`, `channel_id`, `stream_id`, `duration`, `byte_count`, `created_at`, `updated_at`)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE `duration` = ?, `byte_count` = ?, `updated_at` = NOW()
    "#,
    )
    .bind(user_id)
    .bind(channel_id)
    .bind(stream_id)
    .bind(duration)
    .bind(byte_count)
    .bind(duration)
    .bind(byte_count);

    trace!("Running SQL query: {}", query.sql());

    query.execute(connection.deref_mut()).await?;

    Ok(())
}
