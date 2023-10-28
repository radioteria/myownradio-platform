use crate::data_structures::{StreamId, UserId};
use reqwest::StatusCode;
use std::time::Duration;
use tracing::error;

#[derive(thiserror::Error, Debug)]
pub(crate) enum PubsubClientError {
    #[error(transparent)]
    Reqwest(#[from] reqwest::Error),
}

#[derive(Clone)]
pub(crate) struct PubsubClient {
    client: reqwest::Client,
    endpoint: String,
}

impl PubsubClient {
    pub(crate) fn new(endpoint: &str) -> Self {
        Self {
            client: reqwest::Client::builder()
                .timeout(Duration::from_secs(5))
                .connect_timeout(Duration::from_secs(5))
                .build()
                .expect("Unable to build the client"),
            endpoint: endpoint.to_string(),
        }
    }

    pub(crate) async fn publish_restart_channel_message(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), PubsubClientError> {
        self.publish_message(
            user_id,
            &serde_json::json!({
                "channelId": *channel_id,
                "eventType": "RestartChannel"
            }),
        )
        .await
    }

    pub(crate) async fn publish_outgoing_stream_started_message(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
        stream_id: &str,
    ) -> Result<(), PubsubClientError> {
        self.publish_message(
            user_id,
            &serde_json::json!({
                "channelId": *channel_id,
                "streamId": stream_id,
                "eventType": "OutgoingStreamStarted"
            }),
        )
        .await
    }

    pub(crate) async fn publish_outgoing_stream_stats_message(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
        byte_count: &u64,
        time_position: &u64,
        stream_id: &str,
    ) -> Result<(), PubsubClientError> {
        self.publish_message(
            user_id,
            &serde_json::json!({
                "channelId": *channel_id,
                "streamId": stream_id,
                "eventType": "OutgoingStreamStats",
                "byteCount": byte_count,
                "timePosition": time_position,
            }),
        )
        .await
    }

    pub(crate) async fn publish_outgoing_stream_error_message(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
        stream_id: &str,
    ) -> Result<(), PubsubClientError> {
        self.publish_message(
            user_id,
            &serde_json::json!({
                "channelId": *channel_id,
                "streamId": stream_id,
                "eventType": "OutgoingStreamError"
            }),
        )
        .await
    }

    pub(crate) async fn publish_outgoing_stream_finished_message(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
        stream_id: &str,
    ) -> Result<(), PubsubClientError> {
        self.publish_message(
            user_id,
            &serde_json::json!({
                "channelId": *channel_id,
                "streamId": stream_id,
                "eventType": "OutgoingStreamFinished"
            }),
        )
        .await
    }

    async fn publish_message(
        &self,
        user_id: &UserId,
        message: &serde_json::Value,
    ) -> Result<(), PubsubClientError> {
        let client = reqwest::Client::new();
        let response = client
            .post(format!("{}/channel/user/publish", self.endpoint))
            .header("User-Id", **user_id)
            .json(message)
            .send()
            .await?;

        let status = response.status();

        if !matches!(status, StatusCode::OK) {
            let body = response.text().await;

            error!(?status, ?body, "Unexpected response")
        }

        Ok(())
    }
}
