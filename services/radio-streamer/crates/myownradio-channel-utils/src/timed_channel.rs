use crate::channel::{Channel, ChannelClosed};
use crate::timeout::{timer, TimerHandle};
use std::iter::Iterator;
use std::sync::{mpsc, Arc, RwLock};
use std::time::Duration;

/// A struct that is used to send and receive messages of type `T` over a channel
/// with a specified timeout and buffer size.
#[derive(Clone)]
pub struct TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// The time until the channel closes
    timeout: Duration,
    /// The buffer size of the channel
    buffer: usize,
    /// Represents the state of the channel (open or closed)
    is_closed: Arc<RwLock<bool>>,
    /// Holds the list of senders for the channel
    txs: Arc<RwLock<Vec<mpsc::SyncSender<T>>>>,
    /// Holds the handle of the timer
    timer_handle: Arc<RwLock<Option<TimerHandle>>>,
}

impl<T> Channel<T> for TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// A method that sends a message `t` of type `T` to all the receivers of the channel.
    /// If the channel is closed, it returns an error.
    ///
    /// # Examples
    /// ```
    /// use std::time::Duration;
    /// use myownradio_channel_utils::{Channel, TimedChannel};
    ///
    /// let channel = TimedChannel::new(Duration::from_secs(60), 10);
    /// let _ = channel.send("Hello World").unwrap();
    ///
    /// ```
    ///
    /// # Errors
    /// If the channel is closed, it will return an error of `ChannelError::ChannelClosed`
    fn send(&self, t: T) -> Result<(), ChannelClosed> {
        if self.is_closed() {
            return Err(ChannelError::ChannelClosed);
        }

        let mut txs = self
            .txs
            .write()
            .expect("Failed to acquire write lock on txs");

        txs.retain_mut(|tx| tx.try_send(t.clone()).is_ok());

        if txs.is_empty() && self.timer_handle.read().unwrap().is_none() {
            self.start_timer();
        }

        Ok(())
    }

    /// A method that creates a receiver for the channel. If the channel is closed, it returns an error.
    ///
    /// # Examples
    /// ```
    /// use std::time::Duration;
    /// use myownradio_channel_utils::{Channel, TimedChannel};
    ///
    /// let channel = TimedChannel::<()>::new(Duration::from_secs(60), 10);
    /// let receiver = channel.subscribe().unwrap();
    ///
    /// ```
    ///
    /// # Errors
    /// If the channel is closed, it will return an error of `ChannelError::ChannelClosed`
    fn subscribe<I>(&self) -> Result<I, ChannelClosed>
    where
        I: Iterator<Item = T>,
    {
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
        self.is_closed.read().unwrap().clone()
    }
}

impl<T> TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// A constructor method that creates a new `TimedChannel` struct.
    /// It takes in `timeout` and `buffer` as parameters and initializes the fields accordingly.
    ///
    /// It also starts the timer for the channel so that it will automatically close
    /// after the specified duration if there are no receivers are created.
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
