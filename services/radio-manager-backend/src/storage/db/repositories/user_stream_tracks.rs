use crate::models::types::StreamId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::user_tracks::GetUserTracksParams;

#[derive(sqlx::FromRow)]
struct TrackFileLinkMergedRow {}

pub(crate) async fn get_user_tracks(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
    params: &GetUserTracksParams,
    offset: &u32,
) -> RepositoryResult<Vec<TrackFileLinkMergedRow>> {
    Ok(vec![])
}
