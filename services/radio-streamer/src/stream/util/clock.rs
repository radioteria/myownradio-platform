use super::channels::TimedMessage;
use crate::stream::util::time::subtract_abs;
use std::time::{Duration, SystemTime};

/// A struct for syncing the timing of messages being sent.
#[derive(Debug)]
pub(crate) struct MessageSyncClock {
    initial_time: SystemTime,
    position: Duration,
    previous_pts: Duration,
}

impl MessageSyncClock {
    /// Initializes a new instance of the `SyncClock` struct with the given initial_time.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let initial_time = SystemTime::now();
    /// let clock = SyncClock::init(initial_time);
    /// ```
    pub(crate) fn init(initial_time: SystemTime) -> Self {
        Self {
            initial_time,
            position: Duration::ZERO,
            previous_pts: Duration::ZERO,
        }
    }

    /// Returns the total elapsed time since the clock was initialized.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let initial_time = SystemTime::now();
    /// let clock = SyncClock::init(initial_time);
    ///
    /// let elapsed = clock.elapsed();
    /// ```
    pub(crate) fn elapsed(&self) -> SystemTime {
        self.initial_time + self.position
    }

    /// Returns the current position of the clock.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    ///
    /// let initial_time = SystemTime::now();
    /// let clock = SyncClock::init(initial_time);
    ///
    /// let position = clock.position();
    /// ```
    pub(crate) fn position(&self) -> &Duration {
        &self.position
    }

    /// Takes an asynchronous TimedMessage as input, and causes
    /// the current task to sleep for the duration of time until
    /// the next message should be sent.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    /// use super::channels::TimedMessage;
    ///
    /// let initial_time = SystemTime::now();
    /// let mut clock = SyncClock::init(initial_time);
    /// let timed_msg = TimedMessage::new();
    ///
    /// clock.wait(timed_msg);
    /// ```
    pub(crate) async fn wait<'m>(&mut self, timed_msg: impl TimedMessage + 'm) {
        let msg_pts = *timed_msg.pts();
        self.position += subtract_abs(msg_pts, self.previous_pts);
        self.previous_pts = msg_pts;

        let sleep_dur = self.elapsed().duration_since(SystemTime::now()).ok();

        if let Some(duration) = sleep_dur {
            actix_rt::time::sleep(duration).await;
        }
    }

    /// Resets pts monotonic counter. This method should be called
    /// if expecting that the next timed message will have zero pts.
    ///
    /// # Examples
    ///
    /// ```
    /// use std::time::SystemTime;
    /// use super::channels::TimedMessage;
    ///
    /// let initial_time = SystemTime::now();
    /// let mut clock = SyncClock::init(initial_time);
    ///
    /// clock.wait(TimedMessage::new()); // pts: 0.00000s
    /// clock.wait(TimedMessage::new()); // pts: 0.00010s
    /// ...
    /// ...
    /// ...
    /// clock.wait(TimedMessage::new()); // pts: 999.99999s
    ///
    /// clock.reset_next_pts();
    ///
    /// clock.wait(TimedMessage::new()); // pts: 0.00000s
    /// clock.wait(TimedMessage::new()); // pts: 0.00010s
    /// ...
    pub(crate) fn reset_next_pts(&mut self) {
        self.previous_pts = Duration::ZERO;
    }
}
