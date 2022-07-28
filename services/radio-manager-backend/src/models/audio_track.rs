use crate::models::types::{FileId, TrackId, UserId};
use serde::Serialize;
use sqlx::FromRow;

#[derive(Clone, Serialize, FromRow)]
pub(crate) struct AudioTrack {
    tid: TrackId,
    #[serde(skip_serializing)]
    file_id: Option<FileId>,
    #[serde(skip_serializing)]
    uid: UserId,
    filename: String,
    #[serde(skip_serializing)]
    hash: String,
    #[serde(skip_serializing)]
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
    #[serde(skip_serializing)]
    filesize: i32,
    color: i32,
    #[serde(skip_serializing)]
    uploaded: i32,
    #[serde(skip_serializing)]
    copy_of: Option<i32>,
    #[serde(skip_serializing)]
    used_count: i32,
    is_new: bool,
    can_be_shared: bool,
    #[serde(skip_serializing)]
    is_deleted: bool,
    #[serde(skip_serializing)]
    deleted: Option<i32>,
}
