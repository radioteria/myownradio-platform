use crate::models::types::{FileId, StreamId, TrackId, UserId};
use serde::Serialize;
use serde_repr::Serialize_repr;
use sqlx::mysql::MySqlRow;
use sqlx::{FromRow, Row};

#[derive(Clone, Serialize_repr, Debug)]
#[repr(i32)]
pub(crate) enum StreamStatus {
    Stopped,
    Playing,
    Unknown,
}

#[derive(Clone, Serialize, Debug)]
pub(crate) struct Stream {
    pub(crate) sid: StreamId,
    #[serde(skip_serializing)]
    pub(crate) uid: UserId,
    pub(crate) name: String,
    permalink: Option<String>,
    info: String,
    #[serde(skip_serializing)]
    jingle_interval: i32,
    pub(crate) status: StreamStatus,
    pub(crate) started: Option<i64>,
    pub(crate) started_from: Option<i64>,
    access: String,
    category: Option<i32>,
    hashtags: String,
    cover: Option<String>,
    cover_background: Option<String>,
    created: i64,
}

impl From<MySqlRow> for Stream {
    fn from(row: MySqlRow) -> Self {
        Self {
            sid: row.get("sid"),
            uid: row.get("uid"),
            name: row.get("name"),
            permalink: row.get("permalink"),
            info: row.get("info"),
            jingle_interval: row.get("jingle_interval"),
            status: match row.get("status") {
                0 => StreamStatus::Stopped,
                1 => StreamStatus::Playing,
                status => StreamStatus::Unknown,
            },
            started: row.get("started"),
            started_from: row.get("started_from"),
            access: row.get("access"),
            category: row.get("category"),
            hashtags: row.get("hashtags"),
            cover: row.get("cover"),
            cover_background: row.get("cover_background"),
            created: row.get("created"),
        }
    }
}
