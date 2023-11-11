use crate::data_structures::UserId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::UserRow;
use sqlx::{query, query_as, Execute, Row};
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

pub(crate) async fn create_user(
    connection: &mut MySqlConnection,
    email: &str,
    password: &str,
) -> RepositoryResult<UserRow> {
    let login = uuid::Uuid::new_v4().to_string().replace("-", "");

    let insert_query = query(r#"

            INSERT INTO `r_users` 
            (`mail`, `login`, `password`, `name`, `country_id`, `info`, `rights`, `registration_date`, `last_visit_date`, `permalink`, `avatar`)
            VALUES
            (?, ?, ?, "", 0, "", 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), "", "")

    "#).bind(email). bind(&login).bind(password);

    insert_query.execute(connection.deref_mut()).await?;

    let last_insert_id = query_as::<_, (u32,)>("SELECT LAST_INSERT_ID();")
        .fetch_one(connection.deref_mut())
        .await
        .unwrap()
        .0;

    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_users`.`uid` = ");
    builder.push_bind(last_insert_id);
    builder.push(" LIMIT 1");

    let select_query = builder.build_query_as();

    Ok(select_query.fetch_one(connection.deref_mut()).await?)
}

pub(crate) async fn update_user_password(
    connection: &mut MySqlConnection,
    user_id: &UserId,
    password: &str,
) -> RepositoryResult<bool> {
    query("UPDATE `r_users` SET `password` = ? WHERE `mail` = ?")
        .bind(password)
        .bind(user_id)
        .execute(connection.deref_mut())
        .await?;

    let row_count = query_as::<_, (u32,)>("SELECT ROW_COUNT();")
        .fetch_one(connection.deref_mut())
        .await
        .unwrap()
        .0;

    Ok(row_count != 0)
}
