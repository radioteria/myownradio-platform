extern crate serde_millis;

use reqwest::StatusCode;
use serde::{Deserialize, Serialize};
use std::time::{Duration, SystemTime, UNIX_EPOCH};

const REQUEST_TIMEOUT: Duration = Duration::from_secs(5);

#[derive(Deserialize, Debug, Serialize)]
pub struct CurrentTrack {
    #[serde(with = "serde_millis")]
    pub offset: Duration,
    pub title: String,
    pub url: String,
    #[serde(with = "serde_millis")]
    pub duration: Duration,
}

#[derive(Deserialize, Debug, Serialize)]
pub struct NextTrack {
    pub title: String,
    pub url: String,
    #[serde(with = "serde_millis")]
    pub duration: Duration,
}

#[derive(Deserialize, Debug)]
pub struct NowPlaying {
    pub playlist_position: usize,
    pub current_track: CurrentTrack,
    pub next_track: NextTrack,
}

#[derive(Deserialize, Debug)]
pub struct GetNowPlayingResponse {
    pub code: u8,
    pub message: String,
    pub data: NowPlaying,
}

#[derive(Deserialize, Debug, Clone)]
pub struct ChannelInfo {
    pub name: String,
    pub status: u8,
}

#[derive(Deserialize, Debug)]
pub struct GetChannelInfoResponse {
    pub code: u8,
    pub message: String,
    pub data: Option<ChannelInfo>,
}

#[derive(Clone)]
pub struct BackendClient {
    mor_backend_url: String,
}

#[derive(thiserror::Error, Debug)]
pub enum GetNowPlayingError {
    #[error(transparent)]
    RequestError(#[from] reqwest::Error),
    #[error("Channel {0} not found")]
    ChannelNotFound(usize),
    #[error("Unexpected response: {0:?}")]
    UnexpectedResponse(GetNowPlayingResponse),
}

#[derive(thiserror::Error, Debug)]
pub enum GetChannelInfoError {
    #[error(transparent)]
    RequestError(#[from] reqwest::Error),
    #[error("Channel {0} not found")]
    ChannelNotFound(usize),
    #[error("Unexpected response: {0:?}")]
    UnexpectedResponse(GetChannelInfoResponse),
}

impl BackendClient {
    pub fn new(mor_backend_url: &str) -> Self {
        Self {
            mor_backend_url: mor_backend_url.to_string(),
        }
    }

    pub async fn get_now_playing(
        &self,
        channel_id: &usize,
        time: &SystemTime,
    ) -> Result<NowPlaying, GetNowPlayingError> {
        let client = reqwest::Client::builder()
            .timeout(REQUEST_TIMEOUT)
            .build()
            .expect("Unable to initialize HTTP client");

        let unix_time = time.duration_since(UNIX_EPOCH).unwrap().as_millis();

        let url = format!(
            "{}/internal/radio-streamer/v0/streams/{}/playing-at/{}",
            &self.mor_backend_url, channel_id, &unix_time,
        );

        let response: GetNowPlayingResponse = client
            .get(url)
            .send()
            .await?
            .error_for_status()
            .map_err(|error| {
                if matches!(error.status(), Some(StatusCode::NOT_FOUND)) {
                    GetNowPlayingError::ChannelNotFound(*channel_id)
                } else {
                    error.into()
                }
            })?
            .json()
            .await?;

        match response {
            GetNowPlayingResponse {
                code,
                message,
                data,
            } if (code == 1 && message == "OK") => Ok(data),
            GetNowPlayingResponse { .. } => Err(GetNowPlayingError::UnexpectedResponse(response)),
        }
    }

    pub async fn get_channel_info(
        &self,
        channel_id: &usize,
        client_id: Option<String>,
    ) -> Result<ChannelInfo, GetChannelInfoError> {
        let client = reqwest::Client::builder()
            .timeout(REQUEST_TIMEOUT)
            .build()
            .expect("Unable to initialize HTTP client");

        let url = format!(
            "{}/pub/v0/streams/{}/info?client_id={}",
            &self.mor_backend_url,
            &channel_id,
            &client_id.unwrap_or_default(),
        );

        let response: GetChannelInfoResponse = client
            .get(url)
            .timeout(Duration::from_secs(5))
            .send()
            .await?
            .error_for_status()
            .map_err(|error| {
                if matches!(error.status(), Some(StatusCode::NOT_FOUND)) {
                    GetChannelInfoError::ChannelNotFound(*channel_id)
                } else {
                    error.into()
                }
            })?
            .json()
            .await?;

        match response {
            GetChannelInfoResponse {
                code,
                message,
                data: None,
            } if (code == 0 && message == "Stream not found") => {
                return Err(GetChannelInfoError::ChannelNotFound(*channel_id));
            }
            GetChannelInfoResponse {
                code,
                message,
                data: Some(data),
            } if (code == 1 && message == "OK") => Ok(data),
            GetChannelInfoResponse { .. } => Err(GetChannelInfoError::UnexpectedResponse(response)),
        }
    }
}
