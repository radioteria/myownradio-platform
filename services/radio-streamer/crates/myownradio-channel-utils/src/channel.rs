use std::iter::Iterator;

/// Error type that is used to indicate that the channel is closed
#[derive(Debug)]
pub struct ChannelClosed;

pub trait Channel<T>
where
    T: Clone + Send + Sync + 'static,
{
    type Iter: Iterator<Item = T>;

    /// Send a message to all subscribers.
    fn send(&self, msg: T) -> Result<(), ChannelClosed>;

    /// Subscribes to the channel, returning an iterator of messages.
    ///
    /// If the channel is closed, returns a `ChannelClosed` error.
    fn subscribe(&self) -> Result<Self::Iter, ChannelClosed>;

    /// Closes the channel and removes all subscribers.
    ///
    /// After the channel is closed, all subsequent attempts to send or subscribe will fail.
    fn close(&self);

    /// Returns whether the channel is closed or not.
    fn is_closed(&self) -> bool;
}
