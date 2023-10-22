use crate::data_structures::{StreamId, UserId};
use reqwest::Client;
use serde::Deserialize;
use serde_json::json;
use tracing::error;

#[derive(Debug, Deserialize)]
pub(crate) enum StreamStatus {
    Starting,
    Running,
    Finished,
    Failed,
    Unknown,
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamEntry {
    pub(crate) channel_id: StreamId,
    pub(crate) stream_id: String,
    pub(crate) status: StreamStatus,
}

pub(crate) struct RtmpSettings {
    pub(crate) rtmp_url: String,
    pub(crate) stream_key: String,
}

pub(crate) struct VideoSettings {
    pub(crate) width: u32,
    pub(crate) height: u32,
    pub(crate) bitrate: u32,
    pub(crate) framerate: u32,
}

pub(crate) struct AudioSettings {
    pub(crate) bitrate: u32,
    pub(crate) channels: u8,
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
        stream_id: &str,
        rtmp_settings: &RtmpSettings,
        video_settings: &VideoSettings,
        audio_settings: &AudioSettings,
    ) -> Result<(), WebEgressControllerClientError> {
        let json = json!({
            "streamId": stream_id,
            "channelId": **channel_id,
            "webpageUrl": "https://radioter.io/new/player/118?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJleHAiOjE3OTc0ODM4NjgsImNsYWltcyI6W3sibWV0aG9kcyI6WyJHRVQiXSwidXJpcyI6WyIvIl19XX0.dwzeNu9cqwEbrI-HMon72RXYmshdUMNUycBsmQ7JZqE",
            "rtmpSettings": {
                "rtmpUrl": rtmp_settings.rtmp_url,
                "streamKey": rtmp_settings.stream_key
            },
            "videoSettings": {
                "width": video_settings.width,
                "height": video_settings.height,
                "bitrate": video_settings.bitrate,
                "framerate": video_settings.framerate
            },
            "audioSettings": {
                "bitrate": audio_settings.bitrate,
                "channels": audio_settings.channels
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
    ) -> Result<StreamEntry, WebEgressControllerClientError> {
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
