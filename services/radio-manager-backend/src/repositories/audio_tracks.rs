use crate::models::audio_track::{AudioTrack, StreamTracksEntry};
use crate::models::types::{StreamId, UserId};
use crate::mysql_client::{MySqlClient, MySqlConnection};
use crate::repositories::stream_audio_tracks::get_playlist_duration;
use crate::repositories::DEFAULT_TRACKS_PER_REQUEST;
use serde_repr::{Deserialize_repr, Serialize_repr};
use sqlx::{
    query, query_as, Acquire, Database, Error, Execute, MySql, MySqlExecutor, QueryBuilder, Row,
    Type,
};
use std::ops::{Deref, DerefMut};
use tracing::trace;

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

fn create_audio_tracks_builder<'a>() -> QueryBuilder<'a, MySql> {
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
       `fs_file`.`file_extension`
FROM `r_tracks` 
JOIN `fs_file` ON `fs_file`.`file_id` = `r_tracks`.`file_id`
"#,
    )
}

pub(crate) async fn get_user_audio_tracks(
    mut conn: &mut MySqlConnection,
    user_id: &UserId,
    color: &Option<u32>,
    filter: &Option<String>,
    offset: &u32,
    unused: &bool,
    sorting_column: &SortingColumn,
    sorting_order: &SortingOrder,
) -> Result<Vec<AudioTrack>, Error> {
    let mut builder = create_audio_tracks_builder();

    builder.push(" WHERE `r_tracks`.`uid` = ");
    builder.push_bind(user_id.deref());

    if let Some(filter) = filter {
        if !filter.is_empty() {
            builder.push(
                " AND MATCH(`r_tracks`.`artist`, `r_tracks`.`title`, `r_tracks`.`genre`) AGAINST (",
            );
            builder.push_bind(filter);
            builder.push(" IN BOOLEAN MODE)");
        }
    };

    if let Some(color) = color {
        builder.push(" AND `r_tracks`.`color` = ");
        builder.push_bind(color);
    };

    if *unused {
        builder.push(" AND `r_tracks`.`used_count` = 0");
    }

    builder.push(format_args!(
        " ORDER BY {} {}",
        sorting_column.as_str(),
        sorting_order.as_str()
    ));

    builder.push(" LIMIT ");
    builder.push_bind(offset);
    builder.push(", ");
    builder.push_bind(DEFAULT_TRACKS_PER_REQUEST);

    let query = builder.build();

    trace!("Running SQL query: {}", query.sql());

    let audio_tracks = query
        .fetch_all(conn.deref_mut())
        .await
        .map(|rows| rows.iter().map(Into::into).collect())?;

    Ok(audio_tracks)
}
