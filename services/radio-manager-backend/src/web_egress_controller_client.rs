use crate::data_structures::{StreamId, UserId};
use reqwest::Client;
use serde::{Deserialize, Serialize};
use serde_json::json;
use tracing::{error, trace};

#[derive(Debug, Deserialize, Serialize)]
pub(crate) enum StreamStatus {
    Starting,
    Running,
    Finished,
    Failed,
    Unknown,
}

#[derive(Deserialize, Serialize)]
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
    stream_player_url_prefix: String,
    client: Client,
}

impl WebEgressControllerClient {
    pub(crate) fn new(endpoint: &str, stream_player_url_prefix: &str) -> Self {
        let client = Client::new();
        let endpoint = endpoint.to_string();
        let stream_player_url_prefix = stream_player_url_prefix.to_string();

        Self {
            endpoint,
            stream_player_url_prefix,
            client,
        }
    }

    pub(crate) async fn start_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
        stream_id: &str,
        token: &str,
        rtmp_settings: &RtmpSettings,
        video_settings: &VideoSettings,
        audio_settings: &AudioSettings,
    ) -> Result<(), WebEgressControllerClientError> {
        let webpage_url = format!(
            "{}/{}?token={}",
            self.stream_player_url_prefix, **channel_id, token
        );

        let json = json!({
            "streamId": stream_id,
            "channelId": **channel_id,
            "webpageUrl": webpage_url,
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

        let response = self
            .client
            .post(format!("{}/users/{}/streams", self.endpoint, **user_id))
            .json(&json)
            .send()
            .await?
            .error_for_status()?
            .text()
            .await?;

        trace!("Start stream response={}", response);

        Ok(())
    }

    pub(crate) async fn stop_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), WebEgressControllerClientError> {
        let response = self
            .client
            .delete(format!(
                "{}/users/{}/streams/{}",
                self.endpoint, **user_id, **channel_id
            ))
            .send()
            .await?
            .error_for_status()?
            .text()
            .await?;

        trace!("Stop stream response={}", response);

        Ok(())
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
