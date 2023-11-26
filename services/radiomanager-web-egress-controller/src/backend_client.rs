use crate::stream_events::StreamEvent;
use crate::types::UserId;
use reqwest::Client;
use serde_json::json;
use std::time::Duration;
use tracing::warn;
use uuid::Uuid;

#[derive(Clone)]
pub(crate) struct BackendClient {
    endpoint: String,
    client: Client,
}

impl BackendClient {
    pub(crate) fn create(endpoint: &str) -> Self {
        let endpoint = endpoint.to_string();
        let client = Client::builder()
            .connect_timeout(Duration::from_secs(5))
            .timeout(Duration::from_secs(5))
            .build()
            .expect("Unable to build the client");

        Self { endpoint, client }
    }

    pub(crate) async fn sent_stream_event(
        &self,
        user_id: &UserId,
        channel_id: &u32,
        stream_id: &Uuid,
        event: &StreamEvent,
    ) {
        if let Err(error) = self
            .client
            .post(format!("{}/internal/v0/egress/stream-event", self.endpoint))
            .json(&json!({
                "userId": user_id,
                "channelId": channel_id,
                "streamId": stream_id.to_string(),
                "event": event
            }))
            .send()
            .await
            .and_then(|res| res.error_for_status())
        {
            warn!(?error, "Unable to POST /stream-event");
        }
    }
}
