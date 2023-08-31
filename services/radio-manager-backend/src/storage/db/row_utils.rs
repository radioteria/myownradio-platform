use crate::storage::db::repositories::user_stream_tracks::TrackFileLinkMergedRow;
use crate::storage::db::repositories::user_tracks::TrackFileMergedRow;

pub(crate) trait GetArtistAndTitle {
    fn get_artist_and_title(&self) -> String;
}

pub(crate) trait GetFilePath {
    fn get_file_path(&self) -> String;
}

impl GetArtistAndTitle for TrackFileLinkMergedRow {
    fn get_artist_and_title(&self) -> String {
        format!("{} - {}", self.track.artist, self.track.title)
    }
}

impl GetFilePath for TrackFileLinkMergedRow {
    fn get_file_path(&self) -> String {
        format!(
            "{}/{}/{}.{}",
            &self.file.file_hash[..1],
            &self.file.file_hash[1..2],
            self.file.file_hash,
            self.file.file_extension
        )
    }
}

impl GetArtistAndTitle for TrackFileMergedRow {
    fn get_artist_and_title(&self) -> String {
        format!("{} - {}", self.track.artist, self.track.title)
    }
}

impl GetFilePath for TrackFileMergedRow {
    fn get_file_path(&self) -> String {
        format!(
            "{}/{}/{}.{}",
            &self.file.file_hash[..1],
            &self.file.file_hash[1..2],
            &self.file.file_hash,
            &self.file.file_extension
        )
    }
}
