use crate::models::types::StreamId;
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::{FileRow, LinkRow, TrackRow, DEFAULT_TRACKS_PER_REQUEST};
use sqlx::{Execute, MySql, QueryBuilder};
use std::ops::{Deref, DerefMut};
use tracing::trace;

#[derive(sqlx::FromRow)]
pub(crate) struct TrackFileLinkMergedRow {
    #[sqlx(flatten)]
    pub(crate) track: TrackRow,
    #[sqlx(flatten)]
    pub(crate) file: FileRow,
    #[sqlx(flatten)]
    pub(crate) link: LinkRow,
}

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
       `fs_file`.`use_count`,
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

#[derive(Default, Debug)]
pub(crate) struct GetUserStreamTracksParams {
    pub(crate) color: Option<u32>,
    pub(crate) filter: Option<String>,
}

#[tracing::instrument(err, skip(connection))]
pub(crate) async fn get_user_stream_tracks(
    connection: &mut MySqlConnection,
    stream_id: &StreamId,
    params: &GetUserStreamTracksParams,
    offset: &u32,
) -> RepositoryResult<Vec<TrackFileLinkMergedRow>> {
    let mut builder = create_select_query_builder();

    builder.push(" WHERE `r_link`.`stream_id` = ");
    builder.push_bind(stream_id.deref());

    if let Some(filter) = &params.filter {
        if !filter.is_empty() {
            builder.push(" AND MATCH(artist, title, genre) AGAINST (");
            builder.push_bind(filter);
            builder.push(" IN BOOLEAN MODE)");
        }
    };

    if let Some(color) = params.color {
        builder.push(" AND color = ");
        builder.push_bind(color);
    };

    builder.push(" ORDER BY `r_link`.`t_order`");

    builder.push(" LIMIT ");
    builder.push_bind(offset);
    builder.push(", ");
    builder.push_bind(DEFAULT_TRACKS_PER_REQUEST);

    let query = builder.build_query_as::<TrackFileLinkMergedRow>();

    trace!("Running SQL query: {}", query.sql());

    let stream_audio_tracks = query.fetch_all(connection.deref_mut()).await?;

    Ok(stream_audio_tracks)
}
