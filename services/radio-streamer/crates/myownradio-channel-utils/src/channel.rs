use std::iter::Iterator;
use std::sync::mpsc;

/// Error type that is used to indicate that the channel is closed
#[derive(thiserror::Error, Debug)]
pub enum ChannelError {
    #[error("Channel closed")]
    ChannelClosed,
}

pub trait Channel<T>
where
    T: Clone + Send + Sync + 'static,
{
    fn send(&self, t: T) -> Result<(), ChannelError>;
    fn subscribe<I>(&self) -> Result<I, ChannelError>
    where
        I: Iterator<Item = T>;
    fn close(&self);
    fn is_closed(&self) -> bool;
}
