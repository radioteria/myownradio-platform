use crate::data_structures::UserId;
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
       `destination_json`,
       `created_at`,
       `updated_at`
FROM `stream_destinations`
"#,
    )
}

pub(crate) async fn get_stream_destinations(
    connection: &mut MySqlConnection,
    user_id: &UserId,
) -> RepositoryResult<Vec<StreamDestinationRow>> {
    let mut builder = create_select_query_builder();

    builder.push("WHERE `user_id` = ");
    builder.push_bind(user_id);

    let query = builder.build_query_as::<StreamDestinationRow>();

    trace!("Running SQL query: {}", query.sql());

    let stream_destinations = query.fetch_all(connection.deref_mut()).await?;

    Ok(stream_destinations)
}

pub(crate) async fn update_stream_destination(
    connection: &mut MySqlConnection,
    stream_destination_id: &i32,
    user_id: &UserId,
    destination: &StreamDestination,
) -> RepositoryResult<()> {
    let query = query(
        "UPDATE `stream_destinations` SET `destination_json` = ? WHERE `id` = ? AND `user_id` = ?",
    )
    .bind(serde_json::to_string(destination).expect("Unable to serialize StreamDestination"))
    .bind(stream_destination_id)
    .bind(user_id);

    trace!("Running SQL query: {}", query.sql());

    query.execute(connection.deref_mut()).await?;

    Ok(())
}

pub(crate) async fn create_stream_destination(
    connection: &mut MySqlConnection,
    user_id: &UserId,
    destination: &StreamDestination,
) -> RepositoryResult<()> {
    let query = query(
        "INSERT INTO `stream_destinations` (`user_id`, `destination_json`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?)",
    )
    .bind(user_id)
    .bind(serde_json::to_string(destination).expect("Unable to serialize StreamDestination"))
    .bind(Utc::now())
    .bind(Utc::now());

    trace!("Running SQL query: {}", query.sql());

    query.execute(connection.deref_mut()).await?;

    Ok(())
}

pub(crate) async fn delete_stream_destination(
    connection: &mut MySqlConnection,
    stream_destination_id: &i32,
    user_id: &UserId,
) -> RepositoryResult<()> {
    let query = query("DELETE FROM `stream_destinations` WHERE `id` = ? AND `user_id` = ?")
        .bind(stream_destination_id)
        .bind(user_id);

    trace!("Running SQL query: {}", query.sql());

    query.execute(connection.deref_mut()).await?;

    Ok(())
}
