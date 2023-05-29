use std::ops::Deref;

#[derive(Clone, Debug, PartialEq, Hash, Eq)]
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

impl Into<usize> for ChannelId {
    fn into(self) -> usize {
        self.0 as usize
    }
}
