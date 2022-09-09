use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;

const TABLE_NAME: &str = "r_tracks";

pub(crate) async fn get_user_tracks(conn: &mut MySqlConnection) -> RepositoryResult<()> {}
