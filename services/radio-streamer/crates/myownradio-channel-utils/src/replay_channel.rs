use crate::channel::ChannelClosed;
use crate::Channel;
use std::iter::Iterator;
use std::sync::Mutex;
use std::time::Duration;
use tracing::warn;

pub trait TimedMessage {
    fn time(&self) -> Duration;
}

/// A channel that maintains a replay buffer of items that have been sent through it.
/// Items in the replay buffer are replayed to clients that are late to the channel.
pub(crate) struct ReplayChannel<I, T: Clone + TimedMessage>
where
    I: Channel<T>,
{
    /// The inner channel that is being wrapped.
    inner: I,
    /// The duration of time to keep items in the replay buffer.
    replay_time: Duration,
    /// The replay buffer, which is a vec of items wrapped in a mutex to allow for concurrent access.
    replay_buffer: Mutex<Vec<T>>,
}

impl<I, T> Channel<T> for ReplayChannel<I, T> {
    /// Send an item to the inner channel and add it to the replay buffer.
    fn send(&self, t: T) -> Result<(), ChannelClosed> {
        self.append_to_buffer(t.clone());
        self.inner.send(t)
    }

    fn subscribe<I>(&self) -> Result<I, ChannelClosed>
    where
        I: Iterator<Item = T>,
    {
        let items_receiver = self.inner.subscribe()?;

        let replay_buffer = self.replay_buffer.lock().unwrap().clone();

        Ok(replay_buffer.into_iter().chain(items_receiver))
    }

    fn close(&self) {
        self.inner.close();
        self.replay_buffer.lock().unwrap().clear();
    }

    fn is_closed(&self) -> bool {
        self.inner.is_closed()
    }
}

impl<I, T: TimedMessage + Clone + Sync + Send + 'static> ReplayChannel<I, T> {
    /// Create a new instance of `ReplayTimedChannel`.
    ///
    /// # Arguments
    ///
    /// * `inner` - The inner `TimedChannel` to wrap.
    /// * `replay_time` - The duration of time to keep items in the replay buffer.
    pub(crate) fn new(inner: I, replay_time: Duration) -> Self {
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
                ?message_pts,
                "Replay buffer contains message(s) from the future!"
            )
        }

        guard.retain(|m| m.time().as_secs_f32() >= threshold_millis);
        guard.push(t);
    }
}
