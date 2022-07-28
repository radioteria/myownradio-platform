use crate::models::types::{FileId, TrackId, UserId};
use serde::Serialize;
use sqlx::FromRow;

#[derive(Clone, Serialize, FromRow)]
pub(crate) struct AudioTrack {
    tid: TrackId,
    file_id: Option<FileId>,
    uid: UserId,
    filename: String,
    hash: String,
    ext: String,
    artist: String,
    title: String,
    album: String,
    track_number: String,
    genre: String,
    date: String,
    cue: Option<String>,
    buy: Option<String>,
    duration: i32,
    filesize: i32,
    color: i32,
    uploaded: i32,
    copy_of: Option<i32>,
    used_count: i32,
    is_new: bool,
    can_be_shared: bool,
    is_deleted: bool,
    deleted: Option<i32>,
}
