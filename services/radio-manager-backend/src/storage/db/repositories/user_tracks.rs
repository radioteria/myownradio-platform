use crate::data_structures::{
    SortingColumn, SortingOrder, TrackId, UserId, DEFAULT_TRACKS_PER_REQUEST,
};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::{FileRow, TrackRow};
use sqlx::{query, Execute, FromRow, MySql, QueryBuilder};
use std::ops::{Deref, DerefMut};
use tracing::trace;

fn create_select_query_builder<'a>() -> QueryBuilder<'a, MySql> {
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
       `fs_file`.`server_id`,
       `fs_file`.`use_count`
FROM `r_tracks`
JOIN `fs_file` ON `fs_file`.`file_id` = `r_tracks`.`file_id`
"#,
    )
}

#[derive(FromRow)]
pub(crate) struct TrackFileMergedRow {
    #[sqlx(flatten)]
    pub(crate) track: TrackRow,
    #[sqlx(flatten)]
    pub(crate) file: FileRow,
}

impl Deref for TrackFileMergedRow {
    type Target = TrackId;

    fn deref(&self) -> &Self::Target {
        &self.track.tid
    }
}

#[derive(Default, Debug)]
pub(crate) struct GetUserTracksParams {
    pub(crate) color: Option<u32>,
    pub(crate) filter: Option<String>,
    pub(crate) unused: bool,
    pub(crate) sorting_column: SortingColumn,
    pub(crate) sorting_order: SortingOrder,
}

#[tracing::instrument(err, skip(connection))]
pub(crate) async fn get_user_tracks(
    connection: &mut MySqlConnection,
    user_id: &UserId,
    params: &GetUserTracksParams,
    offset: &u32,
) -> RepositoryResult<Vec<TrackFileMergedRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_tracks`.`uid` = ");
    builder.push_bind(user_id.deref());

    if let Some(filter) = &params.filter {
        if !filter.is_empty() {
            builder.push(
                " AND MATCH(`r_tracks`.`artist`, `r_tracks`.`title`, `r_tracks`.`genre`) AGAINST (",
            );
            builder.push_bind(filter);
            builder.push(" IN BOOLEAN MODE)");
        }
    };

    if let Some(color) = params.color {
        builder.push(" AND `r_tracks`.`color` = ");
        builder.push_bind(color);
    };

    if params.unused {
        builder.push(" AND `r_tracks`.`used_count` = 0");
    }

    builder.push(format_args!(
        " ORDER BY {} {}",
        params.sorting_column.as_str(),
        params.sorting_order.as_str()
    ));

    builder.push(" LIMIT ");
    builder.push_bind(offset);
    builder.push(", ");
    builder.push_bind(DEFAULT_TRACKS_PER_REQUEST);

    let query = builder.build_query_as::<TrackFileMergedRow>();

    trace!("Running SQL query: {}", query.sql());

    let audio_tracks = query.fetch_all(connection.deref_mut()).await?;

    Ok(audio_tracks)
}

#[tracing::instrument(err, skip(connection))]
pub(crate) async fn get_single_user_track(
    connection: &mut MySqlConnection,
    track_id: &TrackId,
) -> RepositoryResult<Option<TrackFileMergedRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_tracks`.`tid` = ");
    builder.push_bind(track_id.deref());

    let query = builder.build_query_as::<TrackFileMergedRow>();

    trace!("Running SQL query: {}", query.sql());

    let audio_track = query.fetch_optional(connection.deref_mut()).await?;

    Ok(audio_track)
}

#[tracing::instrument(err, skip(connection))]
pub(crate) async fn delete_user_track(
    connection: &mut MySqlConnection,
    track_id: &TrackId,
) -> RepositoryResult<()> {
    query("DELETE FROM `r_tracks` WHERE `tid` = ?")
        .bind(track_id.deref())
        .execute(connection.deref_mut())
        .await?;

    Ok(())
}
