use std::fmt::{Display, Formatter};
use std::pin::Pin;

/// Error type that is used to indicate that the channel is closed
#[derive(Debug)]
pub struct ChannelClosed;

impl Display for ChannelClosed {
    fn fmt(&self, f: &mut Formatter<'_>) -> std::fmt::Result {
        write!(f, "ChannelClosed")
    }
}

impl std::error::Error for ChannelClosed {}

#[async_trait::async_trait]
pub trait Channel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// Send a message to all subscribers.
    async fn send(&self, msg: T) -> Result<(), ChannelClosed>;

    /// Subscribes to the channel, returning an iterator of messages.
    ///
    /// If the channel is closed, returns a `ChannelClosed` error.
    fn subscribe(&self) -> Result<Pin<Box<dyn futures::Stream<Item = T>>>, ChannelClosed>;

    /// Closes the channel and removes all subscribers.
    ///
    /// After the channel is closed, all subsequent attempts to send or subscribe will fail.
    fn close(&self);

    /// Returns whether the channel is closed or not.
    fn is_closed(&self) -> bool;
}
