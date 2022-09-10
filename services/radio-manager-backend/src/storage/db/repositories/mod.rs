use crate::models::types::{FileId, StreamId, TrackId, UserId};
use serde_repr::{Deserialize_repr, Serialize_repr};

pub(crate) mod errors;
pub(crate) mod streams;
pub(crate) mod user_stream_tracks;
pub(crate) mod user_tracks;

#[derive(sqlx::FromRow)]
pub(crate) struct TrackRow {
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

#[derive(sqlx::FromRow)]
pub(crate) struct FileRow {
    pub(crate) file_id: FileId,
    pub(crate) file_size: i64,
    pub(crate) file_hash: String,
    pub(crate) file_extension: String,
    pub(crate) server_id: i32,
    pub(crate) use_count: i32,
}

#[derive(sqlx::FromRow)]
pub(crate) struct LinkRow {
    pub(crate) id: i64,
    pub(crate) stream_id: StreamId,
    pub(crate) track_id: TrackId,
    pub(crate) t_order: i32,
    pub(crate) unique_id: String,
    pub(crate) time_offset: i64,
}

#[derive(Serialize_repr, Deserialize_repr, Debug)]
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

#[derive(Serialize_repr, Deserialize_repr, Debug)]
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

// Copied from Defaults.php
pub(crate) const DEFAULT_TRACKS_PER_REQUEST: i64 = 50;
