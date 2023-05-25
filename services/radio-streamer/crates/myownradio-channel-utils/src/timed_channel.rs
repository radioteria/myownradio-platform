use crate::channel::{Channel, ChannelClosed};
use crate::timeout::{timer, TimerHandle};
use std::iter::Iterator;
use std::sync::mpsc::TrySendError;
use std::sync::{mpsc, Arc, RwLock};
use std::time::Duration;
use tracing::debug;

#[derive(Clone)]
pub struct TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// The time until the channel closes.
    timeout: Duration,
    /// The buffer size of the channel.
    buffer: usize,
    /// Represents the state of the channel (open or closed).
    is_closed: Arc<RwLock<bool>>,
    /// Holds the list of senders for the channel.
    txs: Arc<RwLock<Vec<mpsc::SyncSender<T>>>>,
    /// Holds the handle of the timer.
    timer_handle: Arc<RwLock<Option<TimerHandle>>>,
}

impl<T> TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// Creates a new `TimedChannel` struct with the specified timeout and buffer size.
    ///
    /// The buffer size determines how many messages can be stored in the channel at once.
    ///
    /// The channel automatically closes after the specified duration if there are no new subscribers.
    ///
    /// # Examples
    ///
    /// ```rust
    /// use std::time::Duration;
    /// use myownradio_channel_utils::TimedChannel;
    ///
    /// let channel = TimedChannel::new(Duration::from_secs(60), 10);
    /// ```
    pub fn new(timeout: Duration, buffer: usize) -> Self {
        let channel = TimedChannel {
            timeout,
            buffer,
            is_closed: Arc::new(RwLock::new(false)),
            txs: Arc::new(RwLock::new(vec![])),
            timer_handle: Arc::new(RwLock::new(None)),
        };

        channel.start_timer();

        channel
    }

    /// Starts the close timer for the channel.
    ///
    /// The close timer is started when there are no more subscribers to the channel,
    /// and it will automatically close the channel if no new subscribers join within a certain time period.
    fn start_timer(&self) {
        assert!(self.timer_handle.read().unwrap().is_none());

        let timer_handle = timer(
            {
                let is_closed = self.is_closed.clone();
                let timer_handle = self.timer_handle.clone();

                move || {
                    timer_handle.write().unwrap().take();
                    *is_closed.write().unwrap() = true;
                }
            },
            self.timeout,
        );

        self.timer_handle.write().unwrap().replace(timer_handle);
    }

    /// Stops the close timer for the channel if it's running.
    fn stop_timer(&self) {
        if let Some(handle) = self.timer_handle.write().unwrap().take() {
            let _ = handle.cancel();
        }
    }

    /// Checks whether the channel's close timer has been started or not.
    ///
    /// The close timer is started when there are no more subscribers to the channel, and
    /// it will automatically close the channel if no new subscribers join within a certain time period.
    fn timer_started(&self) -> bool {
        self.timer_handle.read().unwrap().is_some()
    }
}

impl<T> Channel<T> for TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    type Iter = mpsc::IntoIter<T>;

    /// Sends a message `t` of type `T` to all the subscribers of the channel.
    ///
    /// If the channel is closed, it returns an error of type `ChannelClosed`.
    ///
    /// # Examples
    ///
    /// ```rust
    /// use std::time::Duration;
    /// use myownradio_channel_utils::{Channel, TimedChannel};
    ///
    /// let channel = TimedChannel::new(Duration::from_secs(60), 10);
    /// let _ = channel.send("Hello World").unwrap();
    /// ```
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    fn send(&self, t: T) -> Result<(), ChannelClosed> {
        if self.is_closed() {
            return Err(ChannelClosed);
        }

        let mut txs = self
            .txs
            .write()
            .expect("Failed to acquire write lock on txs");

        txs.retain_mut(|tx| match tx.try_send(t.clone()) {
            Ok(()) | Err(TrySendError::Full(_)) => true,
            Err(_) => false,
        });

        if txs.is_empty() && !self.timer_started() {
            self.start_timer();
        }

        Ok(())
    }

    /// Creates a subscriber for the channel.
    ///
    /// If the channel is closed, it returns an error of type `ChannelClosed`.
    ///
    /// # Examples
    ///
    /// ```rust
    /// use std::time::Duration;
    /// use myownradio_channel_utils::{Channel, TimedChannel};
    ///
    /// let channel = TimedChannel::<()>::new(Duration::from_secs(60), 10);
    /// let msg_iter = channel.subscribe().unwrap();
    ///
    /// for msg in msg_iter {
    ///     //
    /// }
    /// ```
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    fn subscribe(&self) -> Result<Self::Iter, ChannelClosed> {
        if self.is_closed() {
            return Err(ChannelClosed);
        }

        let (tx, rx) = mpsc::sync_channel(self.buffer);

        self.stop_timer();

        self.txs.write().unwrap().push(tx);

        Ok(rx.into_iter())
    }

    /// Closes the channel and removes all subscribers
    fn close(&self) {
        self.timer_handle.write().unwrap().take();
        *self.is_closed.write().unwrap() = true;
        self.txs.write().unwrap().clear();
    }

    /// Returns the state of the channel whether it is closed or open.
    fn is_closed(&self) -> bool {
        let is_closed = self.is_closed.read().unwrap();

        *is_closed
    }
}
