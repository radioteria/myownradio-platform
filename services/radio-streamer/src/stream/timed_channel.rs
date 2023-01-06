use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use futures::SinkExt;
use std::sync::{Arc, RwLock};
use std::time::Duration;

#[derive(thiserror::Error, Debug)]
pub(crate) enum ChannelError {
    #[error("Channel closed")]
    ChannelClosed,
}

#[derive(Clone)]
pub(crate) struct TimedChannel<T: Clone> {
    // Static
    timeout: Duration,
    buffer: usize,
    // Dynamic
    is_closed: Arc<RwLock<bool>>,
    txs: Arc<RwLock<Vec<mpsc::Sender<T>>>>,
    timer: Arc<RwLock<Option<JoinHandle<()>>>>,
}

impl<T: Clone + Send + Sync + 'static> TimedChannel<T> {
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

    pub(crate) fn create_receiver(&self) -> Result<mpsc::Receiver<T>, ChannelError> {
        if self.is_closed() {
            return Err(ChannelError::ChannelClosed);
        }

        let (tx, rx) = mpsc::channel(self.buffer);

        self.stop_timer();

        self.txs.write().unwrap().push(tx);

        Ok(rx)
    }

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

    fn stop_timer(&self) {
        if let Some(handle) = self.timer.write().unwrap().take() {
            handle.abort();
        }
    }

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
