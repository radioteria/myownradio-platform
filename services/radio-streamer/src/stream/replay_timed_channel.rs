use super::timed_channel::TimedChannel;
use crate::stream::timed_channel::ChannelError;
use futures::{stream, Stream};
use std::sync::Mutex;
use std::time::Duration;

/// Trait for items that can be sent through a `ReplayTimedChannel`.
/// Implementors must provide a method to retrieve a timestamp as a `Duration`.
pub(crate) trait TimedMessage {
    fn pts(&self) -> &Duration;
}

/// A channel that maintains a replay buffer of items that have been sent through it.
/// Items in the replay buffer are replayed to clients that are late to the channel.
pub(crate) struct ReplayTimedChannel<T: Clone + TimedMessage> {
    /// The inner channel that is being wrapped.
    inner: TimedChannel<T>,
    /// The duration of time to keep items in the replay buffer.
    replay_time: Duration,
    /// The replay buffer, which is a vec of items wrapped in a mutex to allow for concurrent access.
    replay_buffer: Mutex<Vec<T>>,
}

impl<T: TimedMessage + Clone + Sync + Send + 'static> ReplayTimedChannel<T> {
    /// Create a new instance of `ReplayTimedChannel`.
    ///
    /// # Arguments
    ///
    /// * `inner` - The inner `TimedChannel` to wrap.
    /// * `replay_time` - The duration of time to keep items in the replay buffer.
    pub(crate) fn new(inner: TimedChannel<T>, replay_time: Duration) -> Self {
        let replay_buffer = Mutex::new(vec![]);

        Self {
            inner,
            replay_time,
            replay_buffer,
        }
    }

    /// Send an item to the inner channel and add it to the replay buffer.
    ///
    /// # Arguments
    ///
    /// * `t` - The item to send.
    ///
    /// # Returns
    ///
    /// A `Result` that is either `Ok(())` if the item was successfully sent and added to the replay buffer,
    /// or `Err(ChannelError)` if there was an error sending the item to the inner channel.
    pub(crate) async fn send_all(&self, t: T) -> Result<(), ChannelError> {
        self.append_to_buffer(t.clone());
        self.inner.send_all(t).await
    }

    /// Create a receiver that combines items from the inner channel and the replay buffer.
    ///
    /// # Returns
    ///
    /// A `Result` that is either `Ok(Stream<Item = T>)` if a receiver was successfully created,
    /// or `Err(ChannelError)` if there was an error creating a receiver for the inner channel.
    pub(crate) fn subscribe(&self) -> Result<impl Stream<Item = T>, ChannelError> {
        let items_receiver = self.inner.subscribe()?;

        let replay_buffer = self.replay_buffer.lock().unwrap().clone();
        let replayed_items = stream::iter(replay_buffer);

        Ok(stream::select(replayed_items, items_receiver))
    }

    /// Appends an item to the replay buffer and removes items that are older than `replay_time`.
    ///
    /// # Arguments
    ///
    /// * `t` - The item to append to the replay buffer.
    fn append_to_buffer(&self, t: T) {
        let mut guard = self.replay_buffer.lock().unwrap();
        let threshold_millis = t.pts().as_secs_f32() - self.replay_time.as_secs_f32();

        guard.retain(|m| m.pts().as_secs_f32() >= threshold_millis);
        guard.push(t);
    }
}
