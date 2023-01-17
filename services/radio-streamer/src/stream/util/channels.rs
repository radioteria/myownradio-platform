use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use futures::{stream, SinkExt, Stream, StreamExt};
use std::sync::{Arc, Mutex, RwLock};
use std::time::{Duration, SystemTime};

/// Trait for items that can be sent through a `ReplayTimedChannel`.
/// Implementors must provide a method to retrieve a timestamp as a `Duration`.
pub(crate) trait TimedMessage {
    fn pts(&self) -> &Duration;
}

/// Error type that is used to indicate that the channel is closed
#[derive(thiserror::Error, Debug)]
pub(crate) enum ChannelError {
    #[error("Channel closed")]
    ChannelClosed,
}

/// A struct that is used to send and receive messages of type `T` over a channel
/// with a specified timeout and buffer size.
#[derive(Clone)]
pub(crate) struct TimedChannel<T: Clone> {
    /// The time until the channel closes
    timeout: Duration,
    /// The buffer size of the channel
    buffer: usize,
    /// Represents the state of the channel (open or closed)
    is_closed: Arc<RwLock<bool>>,
    /// Holds the list of senders for the channel
    txs: Arc<RwLock<Vec<mpsc::Sender<T>>>>,
    /// Holds the handle of the timer
    timer: Arc<RwLock<Option<JoinHandle<()>>>>,
}

impl<T: Clone + Send + Sync + 'static> TimedChannel<T> {
    /// A constructor method that creates a new `TimedChannel` struct.
    /// It takes in `timeout` and `buffer` as parameters and initializes the fields accordingly.
    ///
    /// It also starts the timer for the channel so that it will automatically close
    /// after the specified duration if there are no receivers are created.
    pub(crate) fn new(timeout: Duration, buffer: usize) -> Self {
        let channel = TimedChannel {
            timeout,
            buffer,
            is_closed: Arc::new(RwLock::new(false)),
            txs: Arc::new(RwLock::new(vec![])),
            timer: Arc::new(RwLock::new(None)),
        };

        channel.start_timer();

        channel
    }

    /// A method that sends a message `t` of type `T` to all the receivers of the channel.
    /// If the channel is closed, it returns an error.
    ///
    /// # Examples
    /// ```
    /// use std::time::Duration;
    /// let channel = TimedChannel::new(Duration::from_secs(60), 10);
    /// let _ = channel.send_all("Hello World").unwrap();
    ///
    /// ```
    ///
    /// # Errors
    /// If the channel is closed, it will return an error of `ChannelError::ChannelClosed`
    pub(crate) async fn send_all(&self, t: T) -> Result<(), ChannelError> {
        use futures::executor::block_on;
        use futures::SinkExt;

        if self.is_closed() {
            return Err(ChannelError::ChannelClosed);
        }

        actix_rt::task::spawn_blocking({
            let txs = self.txs.clone();

            move || {
                txs.write()
                    .unwrap()
                    .retain_mut(|tx| block_on(tx.send(t.clone())).is_ok())
            }
        })
        .await
        .unwrap();

        if self.txs.read().unwrap().len() == 0 && self.timer.read().unwrap().is_none() {
            self.start_timer();
        }

        Ok(())
    }

    /// A method that creates a receiver for the channel. If the channel is closed, it returns an error.
    ///
    /// # Examples
    /// ```
    /// use std::time::Duration;
    /// let channel = TimedChannel::new(Duration::from_secs(60), 10);
    /// let receiver = channel.subscribe().unwrap();
    ///
    /// ```
    ///
    /// # Errors
    /// If the channel is closed, it will return an error of `ChannelError::ChannelClosed`
    pub(crate) fn subscribe(&self) -> Result<impl Stream<Item = T>, ChannelError> {
        if self.is_closed() {
            return Err(ChannelError::ChannelClosed);
        }

        let (tx, rx) = mpsc::channel(self.buffer);

        self.stop_timer();

        self.txs.write().unwrap().push(tx);

        Ok(rx)
    }

    /// A private method that starts the timer for the channel,
    /// it ensures that the timer is not already running
    fn start_timer(&self) {
        assert!(self.timer.read().unwrap().is_none());

        let timer_handle = actix_rt::spawn({
            let timeout = self.timeout.clone();
            let state = self.is_closed.clone();
            let timer = self.timer.clone();

            async move {
                actix_rt::time::sleep(timeout).await;
                timer.write().unwrap().take();
                *state.write().unwrap() = true;
            }
        });

        self.timer.write().unwrap().replace(timer_handle);
    }

    /// A private method that stops the timer for the channel
    fn stop_timer(&self) {
        if let Some(handle) = self.timer.write().unwrap().take() {
            handle.abort();
        }
    }

    /// A private method that returns the state of the channel whether it is closed or open.
    fn is_closed(&self) -> bool {
        self.is_closed.read().unwrap().clone()
    }
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

pub(crate) fn timesync<M: TimedMessage + 'static>(
    offset: SystemTime,
) -> (mpsc::Sender<M>, mpsc::Receiver<M>) {
    let (input_sender, mut input_receiver) = mpsc::channel::<M>(0);
    let (mut output_sender, output_receiver) = mpsc::channel::<M>(0);

    actix_rt::spawn({
        let mut pts_offset = Duration::default();

        async move {
            let mut previous_pts = Duration::default();
            while let Some(msg) = input_receiver.next().await {
                let pts = *msg.pts();
                pts_offset += pts - previous_pts;
                previous_pts = pts;

                let sleep_dur = (offset + pts_offset).duration_since(SystemTime::now()).ok();

                if let Some(duration) = sleep_dur {
                    actix_rt::time::sleep(duration).await;
                }

                if let Err(_) = output_sender.send(msg).await {
                    break;
                }
            }
        }
    });

    (input_sender, output_receiver)
}

pub(crate) async fn pipe<T>(
    mut receiver: impl Stream<Item = T> + Unpin,
    mut sender: mpsc::Sender<T>,
) -> Result<(), mpsc::SendError> {
    while let Some(t) = receiver.next().await {
        sender.send(t).await?;
    }

    Ok(())
}

pub(crate) fn pipe_async<T: 'static>(
    receiver: impl Stream<Item = T> + Unpin + 'static,
    sender: mpsc::Sender<T>,
) -> JoinHandle<()> {
    actix_rt::spawn(async move {
        let _ = pipe(receiver, sender).await;
    })
}

#[cfg(test)]
mod tests {
    use super::*;
    use futures::StreamExt;
    use std::time::Duration;

    #[actix_rt::test]
    async fn create_single_receiver() {
        let channel = TimedChannel::new(Duration::from_secs(10), 1);
        let mut rx = channel.subscribe().unwrap();

        let res = channel.send_all("foo").await;

        assert!(res.is_ok());

        assert_eq!(rx.next().await, Some("foo"));
    }

    #[actix_rt::test]
    async fn create_multiple_receivers() {
        let channel = TimedChannel::new(Duration::from_secs(10), 1);
        let mut rx1 = channel.subscribe().unwrap();
        let mut rx2 = channel.subscribe().unwrap();
        let mut rx3 = channel.subscribe().unwrap();

        assert!(channel.send_all("foo").await.is_ok());

        assert_eq!(rx1.next().await, Some("foo"));
        assert_eq!(rx2.next().await, Some("foo"));
        assert_eq!(rx3.next().await, Some("foo"));
    }

    #[actix_rt::test]
    async fn channel_closed_after_timeout_1() {
        let channel = TimedChannel::new(Duration::default(), 1);

        actix_rt::time::sleep(Duration::from_millis(100)).await;

        let res = channel.send_all("foo").await;

        assert!(res.is_err());
    }

    #[actix_rt::test]
    async fn channel_closed_after_timeout_2() {
        let channel = TimedChannel::new(Duration::default(), 1);
        drop(channel.subscribe().unwrap());

        assert!(channel.send_all("foo").await.is_ok());

        actix_rt::time::sleep(Duration::from_millis(100)).await;

        assert!(channel.send_all("foo").await.is_err());
    }

    #[actix_rt::test]
    async fn channel_not_closed_after_timeout_without_send() {
        let channel = TimedChannel::new(Duration::default(), 1);
        drop(channel.subscribe().unwrap());

        actix_rt::time::sleep(Duration::from_millis(100)).await;

        assert!(channel.send_all("foo").await.is_ok());
    }
}
