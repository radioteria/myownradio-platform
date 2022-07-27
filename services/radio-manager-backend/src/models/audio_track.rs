use crate::models::types::{FileId, TrackId, UserId};
use serde::Serialize;

#[derive(Clone, Serialize)]
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
    duration: usize,
    filesize: usize,
    color: usize,
    uploaded: usize,
    copy_of: Option<usize>,
    used_count: usize,
    is_new: bool,
    can_be_shared: bool,
    is_deleted: bool,
    deleted: Option<usize>,
}
