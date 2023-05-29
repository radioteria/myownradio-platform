use crate::channel::ChannelClosed;
use crate::Channel;
use futures::{stream, StreamExt};
use std::pin::Pin;
use std::sync::Mutex;
use std::time::Duration;
use std::vec;
use tracing::warn;

/// A trait that represents a message that has an associated time.
pub trait TimedMessage {
    fn time(&self) -> Duration;
}

/// A channel that maintains a replay buffer of items that have been sent through it.
///
/// Items in the replay buffer are replayed to clients that are late to the channel.
pub struct ReplayChannel<C, T>
where
    C: Channel<T>,
    T: TimedMessage + Clone + Sync + Send + 'static,
{
    /// The inner channel that is being wrapped.
    inner: C,
    /// The duration of time to keep items in the replay buffer.
    replay_time: Duration,
    /// The replay buffer, which is a vec of items wrapped in a mutex to allow for concurrent access.
    replay_buffer: Mutex<Vec<T>>,
}

#[async_trait::async_trait]
impl<C, T> Channel<T> for ReplayChannel<C, T>
where
    C: Channel<T> + Sync + Send,
    T: TimedMessage + Sync + Send + Clone + 'static,
{
    /// Sends an item to the inner channel and adds it to the replay buffer.
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    async fn send(&self, t: T) -> Result<(), ChannelClosed> {
        self.append_to_buffer(t.clone());
        self.inner.send(t).await
    }

    /// Creates a subscriber for the channel.
    ///
    /// If the channel is closed, it returns an error of type `ChannelClosed`.
    ///
    /// # Examples
    ///
    /// ```rust
    /// use std::time::Duration;
    /// use myownradio_channel_utils::{Channel, ReplayChannel, TimedChannel};
    ///
    /// let channel = ReplayChannel::new(TimedChannel::new(Duration::from_secs(60), 10), Duration::from_secs(10));
    /// let receiver = channel.subscribe().unwrap();
    /// ```
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    fn subscribe(&self) -> Result<Pin<Box<dyn futures::Stream<Item = T>>>, ChannelClosed> {
        let replay_buffer = self.replay_buffer.lock().unwrap().clone();
        let replayed_items = stream::iter(replay_buffer);

        let inner_stream = self.inner.subscribe()?;

        Ok(Box::pin(replayed_items.chain(inner_stream)))
    }

    /// Closes the channel and removes all subscribers.
    ///
    /// After the channel is closed, all subsequent attempts to send or subscribe will fail.
    fn close(&self) {
        self.inner.close();
        self.replay_buffer.lock().unwrap().clear();
    }

    /// Returns whether the channel is closed or not.
    fn is_closed(&self) -> bool {
        self.inner.is_closed()
    }
}

impl<IN, T> ReplayChannel<IN, T>
where
    IN: Channel<T> + Sync,
    T: TimedMessage + Clone + Sync + Send + 'static,
{
    /// Create a new instance of `ReplayTimedChannel`.
    ///
    /// # Arguments
    ///
    /// * `inner` - The inner `TimedChannel` to wrap.
    /// * `replay_time` - The duration of time to keep items in the replay buffer.
    pub fn new(inner: IN, replay_time: Duration) -> Self {
        let replay_buffer = Mutex::new(vec![]);

        Self {
            inner,
            replay_time,
            replay_buffer,
        }
    }

    /// Appends an item to the replay buffer and removes items that are older than `replay_time`.
    ///
    /// # Arguments
    ///
    /// * `t` - The item to append to the replay buffer.
    #[tracing::instrument(skip(self, t))]
    fn append_to_buffer(&self, t: T) {
        let mut guard = self.replay_buffer.lock().unwrap();
        let msg_time = t.time();

        let threshold_millis = msg_time.as_secs_f32() - self.replay_time.as_secs_f32();

        if guard.iter().find(|m| m.time() > msg_time).is_some() {
            warn!(
                ?msg_time,
                "Replay buffer contains message(s) from the future!"
            )
        }

        guard.retain(|m| m.time().as_secs_f32() >= threshold_millis);

        guard.push(t);
    }
}
