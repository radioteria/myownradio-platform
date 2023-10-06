use crate::data_structures::{StreamId, UserId};
use reqwest::StatusCode;
use tracing::error;

#[derive(thiserror::Error, Debug)]
pub(crate) enum PubsubClientError {
    #[error(transparent)]
    Reqwest(#[from] reqwest::Error),
}

#[derive(Clone)]
pub(crate) struct PubsubClient {
    endpoint: String,
}

impl PubsubClient {
    pub(crate) fn new(endpoint: &str) -> Self {
        Self {
            endpoint: endpoint.to_string(),
        }
    }

    pub(crate) async fn restart_channel(
        &self,
        channel_id: &StreamId,
        user_id: &UserId,
    ) -> Result<(), PubsubClientError> {
        let client = reqwest::Client::new();
        let response = client
            .post(format!("{}/channel/user/publish", self.endpoint))
            .header("User-Id", **user_id)
            .json(&serde_json::json!({
                "channelId": *channel_id,
                "eventType": "RestartChannel"
            }))
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
