use async_trait::async_trait;
use std::collections::HashSet;
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

#[derive(Clone)]
pub(crate) struct PlaylistProviderPlaylistEntry {
    title: String,
    artist: String,
    album: String,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum PlaylistProvidingError {
    #[error("Unexpected error")]
    Unexpected,
}

#[async_trait]
trait PlaylistProvider {
    async fn get_playlist(
        &self,
        playlist_id: &str,
    ) -> Result<Option<Vec<PlaylistProviderPlaylistEntry>>, PlaylistProvidingError>;
}

//
// Radio Manager
//

pub(crate) struct RadioManagerPlaylistEntry {
    id: String,
    title: String,
    artist: String,
    album: String,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum RadioManagerError {
    #[error("Unexpected error")]
    Unexpected,
}

#[async_trait]
trait RadioManager {
    async fn get_playlist(
        &self,
        playlist_id: &str,
    ) -> Result<Option<Vec<RadioManagerPlaylistEntry>>, RadioManagerError>;
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
        path_to_new_track: &str,
    ) -> Result<Option<AudioMetadata>, AudioMetadataServiceError>;
}

// Processing Context
pub(crate) enum TrackDownloadingStep {
    Initial,
}

pub(crate) struct TrackDownloadingState {
    track: PlaylistProviderPlaylistEntry,
    candidates: Vec<DownloadId>,
    step: TrackDownloadingStep,
}

pub(crate) enum ProcessingStep {
    GetSrcPlaylist,
    FilterNewTracks(Vec<PlaylistProviderPlaylistEntry>),
    DownloadTracks(Vec<TrackDownloadingState>),
}

pub(crate) struct ProcessingContext {
    step: ProcessingStep,
}

pub(crate) struct PlaylistProcessor {
    downloader: Arc<dyn Downloader>,
    playlist_provider: Arc<dyn PlaylistProvider>,
    radio_manager: Arc<dyn RadioManager>,
    audio_metadata_service: Arc<dyn AudioMetadataService>,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum PlaylistProcessingError {
    #[error(transparent)]
    PlaylistProvidingError(#[from] PlaylistProvidingError),
    #[error(transparent)]
    RadioManagerError(#[from] RadioManagerError),
    #[error("Source playlist not found")]
    SourcePlaylistNotFound,
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
    ) -> Result<(), PlaylistProcessingError> {
        loop {
            match &ctx.step {
                ProcessingStep::GetSrcPlaylist => {
                    let src_playlist = self.playlist_provider.get_playlist(src_playlist_id).await?;

                    match src_playlist {
                        Some(src_tracks) => {
                            ctx.step = ProcessingStep::FilterNewTracks(src_tracks);
                            continue;
                        }
                        None => {
                            return Err(PlaylistProcessingError::SourcePlaylistNotFound);
                        }
                    };
                }
                ProcessingStep::FilterNewTracks(tracks_to_filter) => {
                    let dst_playlist = self.radio_manager.get_playlist(dst_playlist_id).await?;

                    let filtered_tracks = match dst_playlist {
                        Some(dst_tracks) => {
                            let dst_tracks_set = dst_tracks
                                .into_iter()
                                .map(|track| {
                                    format!("{}-{}-{}", track.artist, track.album, track.title)
                                })
                                .collect::<HashSet<_>>();

                            tracks_to_filter
                                .into_iter()
                                .filter(move |track| {
                                    let key =
                                        format!("{}-{}-{}", track.artist, track.album, track.title);
                                    !dst_tracks_set.contains(&key)
                                })
                                .cloned()
                                .collect()
                        }
                        None => tracks_to_filter.clone(),
                    };

                    ctx.step = ProcessingStep::DownloadTracks(
                        filtered_tracks
                            .into_iter()
                            .map(|track| TrackDownloadingState {
                                track,
                                candidates: vec![],
                                step: TrackDownloadingStep::Initial,
                            })
                            .collect(),
                    );
                    continue;
                }
                ProcessingStep::DownloadTracks(track_downloads) => {
                    todo!();
                }
            }
        }
    }
}
