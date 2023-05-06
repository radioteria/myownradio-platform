mod channel;
mod timed_channel;
pub(crate) mod timeout;

pub use channel::{Channel, ChannelError};
pub use timed_channel::TimedChannel;
