use crate::models::types::{FileId, TrackId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryResult;
use crate::storage::db::repositories::DEFAULT_TRACKS_PER_REQUEST;
use serde_repr::{Deserialize_repr, Serialize_repr};
use sqlx::{Execute, FromRow, QueryBuilder, Type};
use std::ops::{Deref, DerefMut};
use tracing::trace;

#[derive(FromRow, Type)]
pub(crate) struct RTracksRow {
    pub(crate) tid: TrackId,
    pub(crate) file_id: Option<FileId>,
    pub(crate) uid: UserId,
    pub(crate) filename: String,
    pub(crate) hash: String,
    pub(crate) ext: String,
    pub(crate) artist: String,
    pub(crate) title: String,
    pub(crate) album: String,
    pub(crate) track_number: String,
    pub(crate) genre: String,
    pub(crate) date: String,
    pub(crate) cue: Option<String>,
    pub(crate) buy: Option<String>,
    pub(crate) duration: i64,
    pub(crate) filesize: i64,
    pub(crate) color: i64,
    pub(crate) uploaded: i64,
    pub(crate) copy_of: Option<i64>,
    pub(crate) used_count: i64,
    pub(crate) is_new: bool,
    pub(crate) can_be_shared: bool,
    pub(crate) is_deleted: bool,
    pub(crate) deleted: Option<i64>,
}

#[derive(FromRow, Type)]
pub(crate) struct FsFileRow {
    pub(crate) file_id: FileId,
    pub(crate) file_size: i64,
    pub(crate) file_hash: String,
    pub(crate) file_extension: String,
    pub(crate) server_id: i32,
    pub(crate) use_count: i32,
}

#[derive(FromRow, Type)]
pub(crate) struct RTracksFsFileMergedRow {
    #[sqlx(flatten)]
    pub(crate) track: RTracksRow,
    #[sqlx(flatten)]
    pub(crate) file: FsFileRow,
}

#[derive(Serialize_repr, Deserialize_repr)]
#[repr(u8)]
pub(crate) enum SortingColumn {
    TrackId,
    Title,
    Artist,
    Genre,
    Duration,
}

impl Default for SortingColumn {
    fn default() -> Self {
        Self::TrackId
    }
}

impl SortingColumn {
    fn as_str(&self) -> &str {
        match self {
            SortingColumn::TrackId => "`r_tracks`.`tid`",
            SortingColumn::Title => "`r_tracks`.`title`",
            SortingColumn::Artist => "`r_tracks`.`artist`",
            SortingColumn::Genre => "`r_tracks`.`genre`",
            SortingColumn::Duration => "`r_tracks`.`duration`",
        }
    }
}

#[derive(Serialize_repr, Deserialize_repr)]
#[repr(u8)]
pub(crate) enum SortingOrder {
    Desc,
    Asc,
}

impl Default for SortingOrder {
    fn default() -> Self {
        Self::Desc
    }
}

impl SortingOrder {
    fn as_str(&self) -> &str {
        match self {
            SortingOrder::Desc => "DESC",
            SortingOrder::Asc => "ASC",
        }
    }
}

const USER_TRACKS_TABLE_NAME: &str = "r_tracks";
const USER_FILES_TABLE_NAME: &str = "fs_file";

#[derive(Default)]
pub(crate) struct UserTracksParams {
    pub(crate) color: Option<u32>,
    pub(crate) filter: Option<String>,
    pub(crate) unused: bool,
    pub(crate) sorting_column: SortingColumn,
    pub(crate) sorting_order: SortingOrder,
}

pub(crate) async fn get_user_tracks(
    connection: &mut MySqlConnection,
    user_id: &UserId,
    params: &UserTracksParams,
    offset: &u32,
) -> RepositoryResult<Vec<RTracksFsFileMergedRow>> {
    let mut builder = QueryBuilder::new(
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
       `fs_file`.`file_id`,
       `fs_file`.`file_hash`,
       `fs_file`.`file_size`,
       `fs_file`.`file_extension`,
       `fs_file`.`server_id`,
       `fs_file`.`use_count`
FROM `r_tracks`
JOIN `fs_file` ON `fs_file`.`file_id` = `r_tracks`.`file_id`
"#,
    );

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

    let query = builder.build_query_as::<RTracksFsFileMergedRow>();

    trace!("Running SQL query: {}", query.sql());

    let audio_tracks = query.fetch_all(connection.deref_mut()).await?;

    Ok(audio_tracks)
}
