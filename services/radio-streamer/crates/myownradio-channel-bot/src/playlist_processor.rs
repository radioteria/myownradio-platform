use async_trait::async_trait;
use std::sync::Arc;

//
// Downloader
//
pub(crate) struct DownloadId(String);

pub(crate) enum DownloadingState {
    DOWNLOADING,
    FINISHED,
}

pub(crate) struct DownloadEntry {
    status: DownloadingState,
    files: Vec<String>,
}

pub(crate) enum DownloadingServiceError {
    Unexpected,
}

#[async_trait]
trait Downloader {
    async fn create_download(&self, path: &str) -> Result<DownloadId, DownloadingServiceError>;
    async fn get_download(
        &self,
        download_id: &DownloadId,
    ) -> Result<Option<DownloadEntry>, DownloadingServiceError>;
    async fn delete_download(
        &self,
        download_id: &DownloadId,
    ) -> Result<(), DownloadingServiceError>;
}

//
// Playlist Provider
//

pub(crate) struct PlaylistProviderPlaylistEntry {
    title: String,
    artist: String,
    album: String,
}

pub(crate) enum PlaylistProvidingError {
    Unexpected,
}

#[async_trait]
trait PlaylistProvider {
    async fn get_playlist(
        &self,
        playlist_id: &str,
    ) -> Result<Option<PlaylistProviderPlaylistEntry>, PlaylistProvidingError>;
}

//
// Radio Manager
//

pub(crate) struct RadioManagerPlaylistTrackEntry {
    id: String,
    title: String,
    artist: String,
    album: String,
}

pub(crate) struct RadioManagerPlaylistEntry {
    id: String,
    title: String,
    tracks: Vec<RadioManagerPlaylistTrackEntry>,
}

pub(crate) enum RadioManagerError {
    Unexpected,
}

#[async_trait]
trait RadioManager {
    async fn get_playlist(
        &self,
        playlist_id: &str,
    ) -> Result<Option<RadioManagerPlaylistEntry>, RadioManagerError>;
    async fn add_track_to_playlist(
        &self,
        playlist_id: &str,
        path_to_track: &str,
    ) -> Result<(), RadioManagerError>;
}

// Audio Metadata Service
pub(crate) struct AudioMetadata {
    title: String,
    artist: String,
    album: String,
}

pub(crate) enum AudioMetadataServiceError {
    Unexpected,
}

#[async_trait]
trait AudioMetadataService {
    async fn get_metadata(
        &self,
        path_to_track: &str,
    ) -> Result<Option<AudioMetadata>, AudioMetadataServiceError>;
}

pub(crate) struct ProcessingContext {}

pub(crate) struct PlaylistProcessor {
    downloader: Arc<dyn Downloader>,
    playlist_provider: Arc<dyn PlaylistProvider>,
    radio_manager: Arc<dyn RadioManager>,
    audio_metadata_service: Arc<dyn AudioMetadataService>,
}

impl PlaylistProcessor {
    pub(crate) fn create(
        downloader: Arc<dyn Downloader>,
        playlist_provider: Arc<dyn PlaylistProvider>,
        radio_manager: Arc<dyn RadioManager>,
        audio_metadata_service: Arc<dyn AudioMetadataService>,
    ) -> Self {
        Self {
            downloader,
            playlist_provider,
            radio_manager,
            audio_metadata_service,
        }
    }

    pub(crate) async fn process(
        &self,
        user_id: &u64,
        src_playlist_id: &str,
        dst_playlist_id: &str,
        ctx: &mut ProcessingContext,
    ) {
        todo!();
    }
}
