use crate::channel::{Channel, ChannelClosed};
use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use std::iter::Iterator;
use std::pin::Pin;
use std::sync::{Arc, RwLock};
use std::time::Duration;
use tracing::{debug, trace, warn};

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
    txs: Arc<RwLock<Vec<mpsc::Sender<T>>>>,
    /// Holds the handle of the timer.
    timer_handle: Arc<RwLock<Option<JoinHandle<()>>>>,
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
    pub fn new(timeout: Duration, buffer: usize) -> Self {
        let is_closed = Arc::new(RwLock::new(false));
        let txs = Arc::new(RwLock::new(vec![]));
        let timer_handle = Arc::new(RwLock::new(None));

        let channel = TimedChannel {
            timeout,
            buffer,
            is_closed,
            txs,
            timer_handle,
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

        debug!("Starting the channel close timer");
        let timer_handle = actix_rt::spawn({
            let timeout = self.timeout.clone();
            let is_closed = self.is_closed.clone();
            let timer_handle = self.timer_handle.clone();
            let txs = self.txs.clone();

            async move {
                actix_rt::time::sleep(timeout).await;

                debug!("Closing timed out channel");
                timer_handle.write().unwrap().take();
                *is_closed.write().unwrap() = true;
                txs.write().unwrap().clear();
            }
        });

        self.timer_handle.write().unwrap().replace(timer_handle);
    }

    /// Stops the close timer for the channel if it's running.
    fn stop_timer(&self) {
        if let Some(handle) = self.timer_handle.write().unwrap().take() {
            debug!("Cancelling the channel close timer");
            let _ = handle.abort();
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

#[async_trait::async_trait]
impl<T> Channel<T> for TimedChannel<T>
where
    T: Clone + Send + Sync + 'static,
{
    /// Sends a message `t` of type `T` to all the subscribers of the channel.
    ///
    /// If the channel is closed, it returns an error of type `ChannelClosed`.
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    async fn send(&self, t: T) -> Result<(), ChannelClosed> {
        if self.is_closed() {
            warn!("Attempt to send data to closed channel");
            return Err(ChannelClosed);
        }

        let mut txs = self
            .txs
            .write()
            .expect("Failed to acquire write lock on txs");

        trace!("Sending data to {} subscriber(s)", txs.len());

        txs.retain_mut(|tx| match tx.try_send(t.clone()) {
            Ok(()) => true,
            Err(error) if error.is_full() => {
                debug!(?error, "Dropping data on send attempt to full channel");
                true
            }
            Err(error) if error.is_disconnected() => {
                debug!(?error, "Dropping disconnected subscriber");
                false
            }
            Err(error) => {
                warn!(?error, "Dropping subscriber on unknown send error");
                false
            }
        });

        if txs.is_empty() && !self.timer_started() {
            debug!("No subscribers: starting the channel close timer");
            self.start_timer();
        }

        Ok(())
    }

    /// Creates a subscriber for the channel.
    ///
    /// If the channel is closed, it returns an error of type `ChannelClosed`.
    ///
    /// # Errors
    ///
    /// Returns an error of type `ChannelClosed` if the channel is closed.
    fn subscribe(&self) -> Result<Pin<Box<dyn futures::Stream<Item = T>>>, ChannelClosed> {
        if self.is_closed() {
            warn!("Attempt to subscribe to closed channel");
            return Err(ChannelClosed);
        }

        debug!("New subscriber");
        let (tx, rx) = mpsc::channel(self.buffer);

        if self.timer_started() {
            self.stop_timer();
        }

        self.txs.write().unwrap().push(tx);

        Ok(Box::pin(rx))
    }

    /// Closes the channel and removes all subscribers
    fn close(&self) {
        debug!("Closing channel");
        if self.timer_started() {
            self.stop_timer();
        }

        *self.is_closed.write().unwrap() = true;
        self.txs.write().unwrap().clear();
    }

    /// Returns the state of the channel whether it is closed or open.
    fn is_closed(&self) -> bool {
        let is_closed = self.is_closed.read().unwrap();

        *is_closed
    }
}
