use crate::data_structures::UserId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::LegacySessionRow;
use sqlx::{query, query_as};
use std::ops::DerefMut;

pub(crate) async fn create_legacy_session(
    connection: &mut MySqlConnection,
    user_id: &UserId,
) -> RepositoryResult<LegacySessionRow> {
    let token = uuid::Uuid::new_v4().to_string();
    let session_id = uuid::Uuid::new_v4().to_string();

    let insert_query = query(
        r#"
        INSERT INTO `r_sessions`
        (`uid`, `ip`, `token`, `client_id`, `authorized`, `http_user_agent`, `session_id`, `permanent`, `expires`)
        VALUES
        (?, "", ?, "", NOW(), "", ?, 1, NOW() + INTERVAL 1 MONTH)
    "#
    ).bind(user_id).bind(&token).bind(&session_id);

    insert_query.execute(connection.deref_mut()).await?;

    let select_query = query_as(r#"
        SELECT `uid`, `ip`, `token`, `client_id`, `authorized`, `http_user_agent`, `session_id`, `permanent`, `expires`
        FROM `r_sessions` WHERE `token` = ?
    "#).bind(&token);

    Ok(select_query.fetch_one(connection.deref_mut()).await?)
}

pub(crate) async fn get_legacy_session(
    connection: &mut MySqlConnection,
    token: &str,
) -> RepositoryResult<Option<LegacySessionRow>> {
    let select_query = query_as(r#"
        SELECT `uid`, `ip`, `token`, `client_id`, `authorized`, `http_user_agent`, `session_id`, `permanent`, `expires`
        FROM `r_sessions` WHERE `token` = ?
    "#).bind(&token);

    Ok(select_query.fetch_optional(connection.deref_mut()).await?)
}

pub(crate) async fn prolong_legacy_session(
    connection: &mut MySqlConnection,
    token: &str,
) -> RepositoryResult<()> {
    let select_query =
        query("UPDATE `r_sessions` SET `expires` = NOW() + INTERVAL 1 MONTH WHERE `token` = ?")
            .bind(&token);

    select_query.execute(connection.deref_mut()).await?;

    Ok(())
}

pub(crate) async fn delete_legacy_session(
    connection: &mut MySqlConnection,
    token: &str,
) -> RepositoryResult<()> {
    let select_query = query("DELETE FROM `r_sessions` WHERE `token` = ?").bind(&token);

    select_query.execute(connection.deref_mut()).await?;

    Ok(())
}
