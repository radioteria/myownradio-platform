use crate::data_structures::{StreamId, UserId};
use reqwest::{Client, StatusCode};
use serde::{Deserialize, Serialize};
use serde_json::json;
use tracing::{error, trace};

#[derive(Debug, Deserialize)]
pub(crate) enum StreamStatus {
    Starting,
    Running,
    Error,
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
pub(crate) enum WebEgressControllerError {
    #[error(transparent)]
    Reqwest(#[from] reqwest::Error),
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum StartOutgoingStreamError {
    #[error("Outgoing stream has already been started")]
    AlreadyStarted,
    #[error(transparent)]
    Reqwest(#[from] reqwest::Error),
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum StopOutgoingStreamError {
    #[error("Outgoing stream has already been stopped")]
    AlreadyStopped,
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
    ) -> Result<(), StartOutgoingStreamError> {
        let webpage_url = format!(
            "{}{}?token={}",
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

        let response = match self
            .client
            .post(format!("{}/users/{}/streams", self.endpoint, **user_id))
            .json(&json)
            .send()
            .await?
            .error_for_status()?
            .text()
            .await
        {
            Ok(_) => Ok(()),
            Err(error) if matches!(error.status(), Some(StatusCode::CONFLICT)) => {
                Err(StartOutgoingStreamError::AlreadyStarted)
            }
            Err(error) => Err(StartOutgoingStreamError::Reqwest(error)),
        };

        Ok(())
    }

    pub(crate) async fn stop_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), StopOutgoingStreamError> {
        match self
            .client
            .delete(format!(
                "{}/users/{}/streams/{}",
                self.endpoint, **user_id, **channel_id
            ))
            .send()
            .await?
            .error_for_status()?
            .text()
            .await
        {
            Ok(_) => Ok(()),
            Err(error) if matches!(error.status(), Some(StatusCode::CONFLICT)) => {
                Err(StopOutgoingStreamError::AlreadyStopped)
            }
            Err(error) => Err(error)?,
        }
    }

    pub(crate) async fn get_stream(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<Option<StreamEntry>, WebEgressControllerError> {
        let url = format!(
            "{}/users/{}/streams/{}",
            self.endpoint, **user_id, **channel_id
        );
        let response = self
            .client
            .get(url)
            .send()
            .await?
            .error_for_status()?
            .json::<StreamEntry>()
            .await;

        match response {
            Ok(stream) => Ok(Some(stream)),
            Err(error) if matches!(error.status(), Some(StatusCode::NOT_FOUND)) => Ok(None),
            Err(error) => Err(error)?,
        }
    }
}
