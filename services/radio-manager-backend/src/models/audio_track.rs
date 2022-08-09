use crate::models::types::{FileId, StreamId, TrackId, UserId};
use serde::Serialize;
use sqlx::mysql::MySqlRow;
use sqlx::{FromRow, Row};

#[derive(Clone, Serialize)]
pub(crate) struct AudioFile {
    hash: String,
    size: i32,
    extension: String,
}

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
    pub(crate) title: String,
    album: String,
    track_number: String,
    genre: String,
    date: String,
    cue: Option<String>,
    buy: Option<String>,
    pub(crate) duration: i32,
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
    #[serde(skip_serializing)]
    file: AudioFile,
}

impl From<&MySqlRow> for AudioTrack {
    fn from(row: &MySqlRow) -> Self {
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
            file: AudioFile {
                hash: row.get("file_hash"),
                size: row.get("file_size"),
                extension: row.get("file_extension"),
            },
        }
    }
}

impl AudioTrack {
    pub(crate) fn artist_and_title(&self) -> String {
        format!("{} - {}", self.artist, self.title)
    }

    pub(crate) fn file_path(&self) -> String {
        format!(
            "{}/{}/{}.{}",
            &self.file.hash[..1],
            &self.file.hash[1..2],
            self.file.hash,
            self.file.extension
        )
    }
}

#[derive(Clone, Serialize)]
pub(crate) struct StreamTracksEntry {
    #[serde(skip_serializing)]
    id: i32,
    #[serde(skip_serializing)]
    stream_id: StreamId,
    #[serde(skip_serializing)]
    track_id: TrackId,
    pub(crate) t_order: i16,
    unique_id: String,
    pub(crate) time_offset: i32,
    #[serde(flatten)]
    pub(crate) track: AudioTrack,
}

impl From<&MySqlRow> for StreamTracksEntry {
    fn from(row: &MySqlRow) -> Self {
        Self {
            id: row.get("id"),
            stream_id: row.get("stream_id"),
            track_id: row.get("track_id"),
            t_order: row.get("t_order"),
            unique_id: row.get("unique_id"),
            time_offset: row.get("time_offset"),
            track: AudioTrack::from(row),
        }
    }
}
