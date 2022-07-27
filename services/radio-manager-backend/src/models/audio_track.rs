use serde::Serialize;

#[derive(Clone, Serialize)]
pub(crate) struct AudioTrack {
    album: String,
    artist: String,
    buy: Option<String>,
    can_be_shared: usize,
    color: usize,
    cue: String,
    date: usize,
    duration: u32,
    filename: String,
    genre: String,
    is_new: usize,
    tid: usize,
    title: String,
    track_number: String,
}
