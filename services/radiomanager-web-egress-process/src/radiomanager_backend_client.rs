use reqwest::Client;
use std::time::Duration;

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
}
