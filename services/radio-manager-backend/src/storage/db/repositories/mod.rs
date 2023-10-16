use crate::data_structures::{FileId, LinkId, OrderId, StreamId, TrackId, UserId};
use serde_repr::Serialize_repr;

pub(crate) mod errors;
pub(crate) mod streams;
pub(crate) mod user_stream_tracks;
pub(crate) mod user_tracks;
pub(crate) mod users;

#[allow(dead_code)]
#[derive(sqlx::FromRow, Clone, Debug)]
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

#[allow(dead_code)]
#[derive(sqlx::FromRow, Clone, Debug)]
pub(crate) struct FileRow {
    pub(crate) file_id: FileId,
    pub(crate) file_size: i64,
    pub(crate) file_hash: String,
    pub(crate) file_extension: String,
    pub(crate) server_id: i32,
    pub(crate) use_count: i32,
}

#[allow(dead_code)]
#[derive(sqlx::FromRow, Clone, Debug)]
pub(crate) struct LinkRow {
    pub(crate) id: LinkId,
    pub(crate) stream_id: StreamId,
    pub(crate) track_id: TrackId,
    pub(crate) t_order: OrderId,
    pub(crate) unique_id: String,
    pub(crate) time_offset: i64,
}

#[derive(sqlx::Type, Clone, Serialize_repr)]
#[repr(i64)]
pub(crate) enum StreamStatus {
    Stopped = 0,
    Playing = 1,
    Paused = 2,
}

#[allow(dead_code)]
#[derive(sqlx::FromRow, Clone)]
pub(crate) struct StreamRow {
    pub(crate) sid: StreamId,
    pub(crate) uid: UserId,
    pub(crate) name: String,
    pub(crate) permalink: Option<String>,
    pub(crate) info: String,
    pub(crate) jingle_interval: i32,
    pub(crate) status: StreamStatus,
    pub(crate) started: Option<i64>,
    pub(crate) started_from: Option<i64>,
    pub(crate) access: String,
    pub(crate) category: Option<i32>,
    pub(crate) hashtags: String,
    pub(crate) cover: Option<String>,
    pub(crate) cover_background: Option<String>,
    pub(crate) created: i64,
}

#[derive(sqlx::FromRow, Clone)]
pub(crate) struct UserRow {
    pub(crate) uid: UserId,
    pub(crate) mail: String,
    pub(crate) login: Option<String>,
    pub(crate) password: Option<String>,
    pub(crate) name: Option<String>,
    pub(crate) country_id: Option<i64>,
    pub(crate) info: Option<String>,
    pub(crate) rights: Option<i64>,
    pub(crate) registration_date: u64,
    pub(crate) last_visit_date: Option<u64>,
    pub(crate) avatar: Option<String>,
}
