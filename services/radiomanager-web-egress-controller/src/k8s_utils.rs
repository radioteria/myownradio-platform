use crate::types::UserId;
use serde::{Deserialize, Serialize};

#[derive(Serialize, Deserialize)]
pub(crate) struct StreamJobMeta {
    pub(crate) user_id: UserId,
    pub(crate) channel_id: u32,
    pub(crate) stream_id: String,
}

pub(crate) fn make_stream_job_name(user_id: &UserId, channel_id: &u32) -> String {
    format!("radioterio-stream-{}-{}", **user_id, channel_id)
}
