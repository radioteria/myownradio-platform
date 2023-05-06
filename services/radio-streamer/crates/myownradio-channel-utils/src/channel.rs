use std::iter::Iterator;
use std::sync::mpsc;

/// Error type that is used to indicate that the channel is closed
#[derive(Debug)]
pub struct ChannelClosed;

pub trait Channel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// Send a message to all subscribers.
    fn send(&self, t: T) -> Result<(), ChannelClosed>;

    /// Subscribes to the channel, returning an iterator of messages.
    ///
    /// If the channel is closed, returns a `ChannelError::ChannelClosed` error.
    fn subscribe<I>(&self) -> Result<I, ChannelClosed>
    where
        I: Iterator<Item = T>;

    /// Closes the channel.
    fn close(&self);

    /// Returns whether the channel is closed or not.
    fn is_closed(&self) -> bool;
}
