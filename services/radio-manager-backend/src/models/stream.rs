use crate::models::types::{FileId, StreamId, TrackId, UserId};
use serde::Serialize;
use sqlx::mysql::MySqlRow;
use sqlx::{FromRow, Row};

#[derive(Clone, Serialize)]
pub(crate) struct Stream {
    sid: StreamId,
    #[serde(skip_serializing)]
    uid: UserId,
    name: String,
    permalink: Option<String>,
    info: String,
    #[serde(skip_serializing)]
    jingle_interval: i32,
    status: i32,
    started: i32,
    started_from: i32,
    access: String,
    category: i32,
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
            status: row.get("status"),
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
