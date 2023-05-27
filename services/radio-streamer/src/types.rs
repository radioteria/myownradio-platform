use std::ops::Deref;

#[derive(Clone, Debug)]
pub(crate) struct ChannelId(u64);

impl Deref for ChannelId {
    type Target = u64;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl From<u64> for ChannelId {
    fn from(channel_id: u64) -> Self {
        Self(channel_id)
    }
}
