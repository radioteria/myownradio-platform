use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use std::sync::{Arc, RwLock};
use std::time::Duration;

#[derive(Debug)]
enum ChannelState {
    Open,
    Closed,
}

#[derive(Debug)]
pub(crate) struct ChannelClosed;

pub(crate) struct TimedChannel<T: Clone> {
    timeout: Duration,
    buffer: usize,
    state: Arc<RwLock<ChannelState>>,
    txs: Arc<RwLock<Vec<mpsc::Sender<T>>>>,
    timer: Arc<RwLock<Option<JoinHandle<()>>>>,
}

impl<T: Clone> TimedChannel<T> {
    pub(crate) fn new(timeout: Duration, buffer: usize) -> Self {
        TimedChannel {
            timeout,
            buffer,
            state: Arc::new(RwLock::new(ChannelState::Open)),
            txs: Arc::new(RwLock::new(vec![])),
            timer: Arc::new(RwLock::new(None)),
        }
    }

    pub(crate) fn send_all(&self, t: T) -> Result<(), ChannelClosed> {
        if self.is_closed() {
            return Err(ChannelClosed);
        }

        self.txs
            .write()
            .unwrap()
            .retain_mut(|tx| tx.try_send(t.clone()).is_err());

        if self.txs.read().unwrap().len() == 0 && self.timer.read().unwrap().is_none() {
            self.start_timer();
        }

        Ok(())
    }

    pub(crate) fn create_receiver(&self) -> Result<mpsc::Receiver<T>, ChannelClosed> {
        if self.is_closed() {
            return Err(ChannelClosed);
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
            let state = self.state.clone();

            async move {
                actix_rt::time::sleep(timeout).await;
                *state.write().unwrap() = ChannelState::Closed;
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
        matches!(*self.state.read().unwrap(), ChannelState::Closed)
    }
}

#[cfg(test)]
mod tests {
    use super::*;
    use futures::StreamExt;

    #[actix_rt::test]
    async fn create_single_consumer() {
        let channel = TimedChannel::new(Duration::from_secs(10), 1);
        let mut rx = channel.create_receiver().unwrap();

        let res = channel.send_all("foo");

        assert!(res.is_ok());

        assert_eq!(rx.next().await, Some("foo"));
    }
}
