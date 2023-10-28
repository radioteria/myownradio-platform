use reqwest::blocking::Client;
use serde_json::json;
use std::time::Duration;
use tracing::warn;
use uuid::Uuid;

pub(crate) struct RadiomanagerBackendClient {
    endpoint: String,
    client: Client,
}

impl RadiomanagerBackendClient {
    pub(crate) fn create(endpoint: &str) -> Self {
        let endpoint = endpoint.to_string();
        let client = Client::builder()
            .connect_timeout(Duration::from_secs(5))
            .timeout(Duration::from_secs(5))
            .build()
            .expect("Unable to build the client");

        Self { endpoint, client }
    }

    pub(crate) fn send_stream_started(&self, stream_id: &Uuid, user_id: &u32, channel_id: &u32) {
        if let Err(error) = self
            .client
            .post(format!(
                "{}/internal/web-egress-process/v0/stream-started",
                self.endpoint
            ))
            .json(&json!({
                "userId": user_id,
                "channelId": channel_id,
                "streamId": stream_id.to_string()
            }))
            .send()
            .and_then(|res| res.error_for_status())
        {
            warn!(?error, "Unable to POST /stream-started");
        }
    }

    pub(crate) fn send_stream_finished(&self, stream_id: &Uuid, user_id: &u32, channel_id: &u32) {
        if let Err(error) = self
            .client
            .post(format!(
                "{}/internal/web-egress-process/v0/stream-finished",
                self.endpoint
            ))
            .json(&json!({
                "userId": user_id,
                "channelId": channel_id,
                "streamId": stream_id.to_string()
            }))
            .send()
            .and_then(|res| res.error_for_status())
        {
            warn!(?error, "Unable to POST /stream-finished");
        }
    }

    pub(crate) fn send_stream_stats(
        &self,
        stream_id: &Uuid,
        user_id: &u32,
        channel_id: &u32,
        byte_count: u64,
        time_position: u64,
    ) {
        if let Err(error) = self
            .client
            .post(format!(
                "{}/internal/web-egress-process/v0/stream-stats",
                self.endpoint
            ))
            .json(&json!({
                "userId": user_id,
                "channelId": channel_id,
                "streamId": stream_id.to_string(),
                "byteCount": byte_count,
                "timePosition": time_position
            }))
            .send()
            .and_then(|res| res.error_for_status())
        {
            warn!(?error, "Unable to POST /stream-stats");
        }
    }

    pub(crate) fn send_stream_error(
        &self,
        stream_id: &Uuid,
        user_id: &u32,
        channel_id: &u32,
        reason: &str,
    ) {
        if let Err(error) = self
            .client
            .post(format!(
                "{}/internal/web-egress-process/v0/stream-error",
                self.endpoint
            ))
            .json(&json!({
                "userId": user_id,
                "channelId": channel_id,
                "streamId": stream_id.to_string(),
                "reason": reason
            }))
            .send()
            .and_then(|res| res.error_for_status())
        {
            warn!(?error, "Unable to POST /stream-error");
        }
    }
}
