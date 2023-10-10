#[derive(Debug, thiserror::Error)]
pub(crate) enum StreamError {}

pub(crate) enum StreamOutput {
    RTMP { url: String, stream_key: String },
}

pub(crate) struct StreamConfig {
    pub(crate) output: StreamOutput,
}

pub(crate) fn create_stream(webpage_url: &str, config: &StreamConfig) -> Result<(), StreamError> {
    Ok(())
}
