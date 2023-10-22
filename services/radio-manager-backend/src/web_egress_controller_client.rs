use crate::data_structures::{StreamId, UserId};
use reqwest::{Client, StatusCode};
use serde::Deserialize;
use serde_json::json;
use tracing::{error, warn};

#[derive(Debug, Deserialize)]
pub(crate) enum WebEgressControllerStreamStatus {
    Starting,
    Running,
    Finished,
    Failed,
    Unknown,
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct WebEgressControllerStreamEntry {
    pub(crate) channel_id: StreamId,
    pub(crate) stream_id: String,
    pub(crate) status: WebEgressControllerStreamStatus,
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum WebEgressControllerClientError {
    #[error(transparent)]
    Reqwest(#[from] reqwest::Error),
}

#[derive(Clone)]
pub(crate) struct WebEgressControllerClient {
    endpoint: String,
    client: Client,
}

impl WebEgressControllerClient {
    pub(crate) fn new(endpoint: &str) -> Self {
        let client = Client::new();
        let endpoint = endpoint.to_string();

        Self { endpoint, client }
    }

    pub(crate) async fn start_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), WebEgressControllerClientError> {
        let stream_id = uuid::Uuid::new_v4();

        let json = json!({
            "stream_id": "118whatever",
            "channel_id": 118,
            "webpage_url": "https://radioter.io/new/player/118?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJleHAiOjE3OTc0ODM4NjgsImNsYWltcyI6W3sibWV0aG9kcyI6WyJHRVQiXSwidXJpcyI6WyIvIl19XX0.dwzeNu9cqwEbrI-HMon72RXYmshdUMNUycBsmQ7JZqE",
            "rtmp_settings": {
                "rtmp_url": "rtmp://live.restream.io/live",
                "stream_key": "re_1394117_053712c0c61e533c5f67"
            },
            "video_settings": {
                "width": 1280,
                "height": 720,
                "bitrate": 2500,
                "framerate": 30
            },
            "audio_settings": {
                "bitrate": 128,
                "channels": 2
            }
        });
        todo!()
    }

    pub(crate) async fn stop_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), WebEgressControllerClientError> {
        todo!()
    }

    pub(crate) async fn get_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<WebEgressControllerStreamEntry, WebEgressControllerClientError> {
        Ok(self
            .client
            .get(format!(
                "{}/users/{}/streams/{}",
                self.endpoint, **user_id, **channel_id
            ))
            .send()
            .await?
            .error_for_status()?
            .json()
            .await?)
    }
}
