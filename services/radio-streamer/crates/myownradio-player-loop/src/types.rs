use std::time::{Duration, SystemTime};

#[derive(Debug, Clone)]
pub struct CurrentTrack {
    pub position: Duration,
    pub duration: Duration,
    pub url: String,
    pub title: String,
}

impl CurrentTrack {
    pub fn remaining_duration(&self) -> Duration {
        self.duration - self.position
    }
}

#[derive(Debug, Clone)]
pub struct NextTrack {
    pub duration: Duration,
    pub url: String,
    pub title: String,
}

#[derive(Debug, Clone)]
pub struct NowPlaying {
    pub current: CurrentTrack,
    pub next: NextTrack,
}

#[derive(Debug, Clone, thiserror::Error)]
pub enum NowPlayingError {
    #[error("NowPlayingError::Retryable")]
    Retryable,
    #[error("NowPlayingError::NonRetryable")]
    NonRetryable,
}

#[trait_async::trait_async]
pub trait NowPlayingClient {
    async fn get_now_playing(
        &self,
        channel_id: &u64,
        time: &SystemTime,
    ) -> Result<NowPlaying, NowPlayingError>;
}
