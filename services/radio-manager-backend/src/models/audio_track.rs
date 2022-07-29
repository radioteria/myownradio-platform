use crate::models::types::{FileId, TrackId, UserId};
use serde::Serialize;
use sqlx::mysql::MySqlRow;
use sqlx::{FromRow, Row};

#[derive(Clone, Serialize)]
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

impl From<MySqlRow> for AudioTrack {
    fn from(row: MySqlRow) -> Self {
        Self {
            tid: row.get("tid"),
            file_id: row.get("file_id"),
            uid: row.get("uid"),
            filename: row.get("filename"),
            hash: row.get("hash"),
            ext: row.get("ext"),
            artist: row.get("artist"),
            title: row.get("title"),
            album: row.get("album"),
            track_number: row.get("track_number"),
            genre: row.get("genre"),
            date: row.get("date"),
            cue: row.get("cue"),
            buy: row.get("buy"),
            duration: row.get("duration"),
            filesize: row.get("filesize"),
            color: row.get("color"),
            uploaded: row.get("uploaded"),
            copy_of: row.get("copy_of"),
            used_count: row.get("used_count"),
            is_new: row.get("is_new"),
            can_be_shared: row.get("can_be_shared"),
            is_deleted: row.get("is_deleted"),
            deleted: row.get("deleted"),
        }
    }
}
