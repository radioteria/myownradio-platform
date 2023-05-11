use std::sync::mpsc;
use std::sync::mpsc::RecvTimeoutError;
use std::time::Duration;
use tracing::{debug, instrument};

pub(crate) struct TimerHandle(Option<mpsc::SyncSender<()>>);

impl TimerHandle {
    pub(crate) fn cancel(mut self) {
        if let Some(sender) = self.0.take() {
            let _ = sender.send(());
        }
    }
}

#[instrument(skip(func))]
pub(crate) fn timer<F>(func: F, timeout: Duration) -> TimerHandle
where
    F: FnOnce() -> () + Send + 'static,
{
    let (s, r) = mpsc::sync_channel(0);

    std::thread::spawn(move || {
        let result = r.recv_timeout(timeout);

        match result {
            Err(RecvTimeoutError::Timeout) => {
                func();
            }
            Err(RecvTimeoutError::Disconnected) => {
                debug!("Cancelled: cancel handle has been dropped");
            }
            Ok(()) => {
                debug!("Cancelled: cancelled by cancel call");
            }
        }
    });

    TimerHandle(Some(s))
}

#[cfg(test)]
mod tests {
    use super::*;
    use std::sync::atomic::{AtomicBool, Ordering};
    use std::sync::Arc;

    #[test]
    fn test_timeout() {
        let timed_out = Arc::new(AtomicBool::new(false));

        let cancel = timer(
            {
                let timed_out = timed_out.clone();

                move || {
                    timed_out.store(true, Ordering::Relaxed);
                }
            },
            Duration::from_millis(100),
        );

        std::thread::sleep(Duration::from_millis(200));

        assert!(timed_out.load(Ordering::Relaxed));

        drop(cancel);
    }

    #[test]
    fn test_cancel_timeout() {
        let timed_out = Arc::new(AtomicBool::new(false));

        let cancel = timer(
            {
                let timed_out = timed_out.clone();

                move || {
                    timed_out.store(true, Ordering::Relaxed);
                }
            },
            Duration::from_millis(100),
        );

        std::thread::sleep(Duration::from_millis(50));

        cancel.cancel();

        std::thread::sleep(Duration::from_millis(50));

        assert!(!timed_out.load(Ordering::Relaxed));
    }

    #[test]
    fn test_cancel_drop() {
        let timed_out = Arc::new(AtomicBool::new(false));

        let cancel = timer(
            {
                let timed_out = timed_out.clone();

                move || {
                    timed_out.store(true, Ordering::Relaxed);
                }
            },
            Duration::from_millis(100),
        );
        drop(cancel);

        std::thread::sleep(Duration::from_millis(100));

        assert!(!timed_out.load(Ordering::Relaxed));
    }
}
