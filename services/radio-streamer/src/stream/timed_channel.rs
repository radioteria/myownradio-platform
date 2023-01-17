use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use futures::SinkExt;
use std::sync::{Arc, RwLock};
use std::time::Duration;

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
    /// let receiver = channel.create_receiver().unwrap();
    ///
    /// ```
    ///
    /// # Errors
    /// If the channel is closed, it will return an error of `ChannelError::ChannelClosed`
    pub(crate) fn create_receiver(&self) -> Result<mpsc::Receiver<T>, ChannelError> {
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

#[cfg(test)]
mod tests {
    use super::*;
    use futures::StreamExt;

    #[actix_rt::test]
    async fn create_single_receiver() {
        let channel = TimedChannel::new(Duration::from_secs(10), 1);
        let mut rx = channel.create_receiver().unwrap();

        let res = channel.send_all("foo").await;

        assert!(res.is_ok());

        assert_eq!(rx.next().await, Some("foo"));
    }

    #[actix_rt::test]
    async fn create_multiple_receivers() {
        let channel = TimedChannel::new(Duration::from_secs(10), 1);
        let mut rx1 = channel.create_receiver().unwrap();
        let mut rx2 = channel.create_receiver().unwrap();
        let mut rx3 = channel.create_receiver().unwrap();

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
        drop(channel.create_receiver().unwrap());

        assert!(channel.send_all("foo").await.is_ok());

        actix_rt::time::sleep(Duration::from_millis(100)).await;

        assert!(channel.send_all("foo").await.is_err());
    }

    #[actix_rt::test]
    async fn channel_not_closed_after_timeout_without_send() {
        let channel = TimedChannel::new(Duration::default(), 1);
        drop(channel.create_receiver().unwrap());

        actix_rt::time::sleep(Duration::from_millis(100)).await;

        assert!(channel.send_all("foo").await.is_ok());
    }
}
