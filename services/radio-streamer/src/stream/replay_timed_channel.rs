use super::timed_channel::TimedChannel;
use crate::stream::timed_channel::ChannelError;
use futures::{stream, Stream};
use std::sync::Mutex;
use std::time::Duration;

pub(crate) trait TimedMessage {
    fn pts(&self) -> &Duration;
}

pub(crate) struct ReplayTimedChannel<T: Clone + TimedMessage> {
    inner: TimedChannel<T>,
    replay_time: Duration,
    replay_buffer: Mutex<Vec<T>>,
}

impl<T: TimedMessage + Clone + Sync + Send + 'static> ReplayTimedChannel<T> {
    pub(crate) fn new(inner: TimedChannel<T>, replay_time: Duration) -> Self {
        let replay_buffer = Mutex::new(vec![]);

        Self {
            inner,
            replay_time,
            replay_buffer,
        }
    }

    pub(crate) async fn send_all(&self, t: T) -> Result<(), ChannelError> {
        self.append_to_buffer(t.clone());
        self.inner.send_all(t).await
    }

    pub(crate) fn create_receiver(&self) -> Result<impl Stream<Item = T>, ChannelError> {
        let items_receiver = self.inner.create_receiver()?;

        let replay_buffer = self.replay_buffer.lock().unwrap().clone();
        let replayed_items = stream::iter(replay_buffer);

        Ok(stream::select(replayed_items, items_receiver))
    }

    fn append_to_buffer(&self, t: T) {
        let mut guard = self.replay_buffer.lock().unwrap();
        let threshold_millis = t.pts().as_secs_f32() - self.replay_time.as_secs_f32();

        guard.retain(|m| m.pts().as_secs_f32() >= threshold_millis);
        guard.push(t);
    }
}
