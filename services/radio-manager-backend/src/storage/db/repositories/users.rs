use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::UserRow;
use sqlx::Execute;
use sqlx::{MySql, QueryBuilder};
use std::ops::DerefMut;
use tracing::trace;

fn create_select_query_builder<'a>() -> QueryBuilder<'a, MySql> {
    QueryBuilder::new(
        r#"
SELECT `r_users`.`uid`,
       `r_users`.`mail`,
       `r_users`.`login`,
       `r_users`.`password`,
       `r_users`.`name`,
       `r_users`.`country_id`,
       `r_users`.`info`,
       `r_users`.`rights`,
       `r_users`.`registration_date`,
       `r_users`.`last_visit_date`,
       `r_users`.`permalink`,
       `r_users`.`avatar`
FROM `r_users`
"#,
    )
}

pub(crate) async fn get_user_by_session_token(
    connection: &mut MySqlConnection,
    session_token: &str,
) -> RepositoryResult<Option<UserRow>> {
    let mut builder = create_select_query_builder();

    builder.push("JOIN `r_sessions` ON `r_sessions`.`uid` = `r_users`.`uid`");

    builder.push(" WHERE `r_sessions`.`token` = ");
    builder.push_bind(session_token);
    builder.push(" LIMIT 1");

    let query = builder.build_query_as();

    trace!("Running SQL query: {}", query.sql());

    let maybe_user = query.fetch_optional(connection.deref_mut()).await?;

    Ok(maybe_user)
}

pub(crate) async fn get_user_by_email(
    connection: &mut MySqlConnection,
    email: &str,
) -> RepositoryResult<Option<UserRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_users`.`mail` = ");
    builder.push_bind(email);
    builder.push(" LIMIT 1");

    let query = builder.build_query_as();

    Ok(query.fetch_optional(connection.deref_mut()).await?)
}
