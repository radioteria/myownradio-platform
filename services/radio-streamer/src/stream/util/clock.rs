use super::channels::TimedMessage;
use std::time::{Duration, SystemTime};

/// A struct for syncing the timing of messages being sent.
#[derive(Debug)]
pub(crate) struct MessageSyncClock {
    offset: SystemTime,
    position: Duration,
    previous_pts: Duration,
}

impl MessageSyncClock {
    /// Initializes a new instance of the `SyncClock` struct with the given offset.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let offset = SystemTime::now();
    /// let clock = SyncClock::init(offset);
    /// ```
    pub(crate) fn init(offset: SystemTime) -> Self {
        Self {
            offset,
            position: Duration::from_secs(0),
            previous_pts: Duration::from_secs(0),
        }
    }

    /// Returns the total elapsed time since the clock was initialized.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let offset = SystemTime::now();
    /// let clock = SyncClock::init(offset);
    ///
    /// let elapsed = clock.elapsed();
    /// ```
    pub(crate) fn elapsed(&self) -> SystemTime {
        self.offset + self.position
    }

    /// Returns the current position of the clock.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let offset = SystemTime::now();
    /// let clock = SyncClock::init(offset);
    ///
    /// let position = clock.position();
    /// ```
    pub(crate) fn position(&self) -> &Duration {
        &self.position
    }

    /// Takes an asynchronous TimedMessage as input, and causes the current task to sleep for the duration of time until the next message should be sent.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    /// use super::channels::TimedMessage;
    ///
    /// let offset = SystemTime::now();
    /// let mut clock = SyncClock::init(offset);
    /// let timed_msg = TimedMessage::new();
    ///
    /// clock.wait(timed_msg);
    /// ```
    pub(crate) async fn wait<'a>(&mut self, timed_msg: impl TimedMessage + 'a) {
        let msg_pts = *timed_msg.pts();
        self.position += msg_pts - self.previous_pts;
        self.previous_pts = msg_pts;

        let sleep_dur = self.elapsed().duration_since(SystemTime::now()).ok();

        if let Some(duration) = sleep_dur {
            actix_rt::time::sleep(duration).await;
        }
    }
}
