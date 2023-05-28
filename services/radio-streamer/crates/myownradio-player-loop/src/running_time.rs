use std::time::Duration;
use tracing::{debug, trace, warn};

/// A struct for tracking the running time of an audio player loop.
#[derive(Debug)]
pub(crate) struct RunningTime {
    /// The current running time.
    time: Duration,
    /// The previous timestamp value.
    previous_pts: Option<Duration>,
}

impl RunningTime {
    /// Initializes a new instance of the `RunningTime` struct.
    pub(crate) fn new() -> Self {
        Self {
            time: Duration::ZERO,
            previous_pts: None,
        }
    }

    /// Returns the current running time value.
    pub(crate) fn time(&self) -> &Duration {
        &self.time
    }

    /// Advances the running time based on the given timestamp value.
    ///
    /// This method should be called for each incoming audio frame to update
    /// the running time value. The `next_pts` value should be a monotonically
    /// increasing timestamp value that starts from `Duration::ZERO`.
    ///
    /// If `next_pts` is less than or equal to the previous timestamp value,
    /// a warning will be logged to help diagnose issues with the incoming frames.
    pub(crate) fn advance_by_next_pts(&mut self, next_pts: &Duration) {
        if let Some(prev_pts) = &self.previous_pts {
            if prev_pts >= next_pts {
                warn!(
                    "Backward-going timestamps detected: prev_pts = {:?}, next_pts = {:?}",
                    prev_pts, next_pts,
                );
            }
        }

        let duration_since_previous =
            subtract_abs(*next_pts, self.previous_pts.unwrap_or(*next_pts));
        trace!("Advance by {:?}", duration_since_previous);
        self.time += duration_since_previous;
        self.previous_pts = Some(*next_pts);
    }

    /// Advances the running time based on the given duration value.
    ///
    /// This method should be called to manually update the running time value
    /// by a specific duration. The `duration` parameter should represent the
    /// time interval to be added to the current running time.
    pub(crate) fn advance_by_duration(&mut self, duration: &Duration) {
        debug!("Advance by {:?}", duration);

        self.time += *duration;
        self.previous_pts = None;
    }

    /// Resets the previous timestamp value to `None`.
    ///
    /// This method should be called if the next incoming frame is expected
    /// to have a timestamp value of `Duration::ZERO`.
    pub(crate) fn reset_pts(&mut self) {
        debug!("Reset previous pts");

        self.previous_pts = None;
    }
}

/// Returns the absolute difference between the given `time1` and `time2`.
///
/// This function returns `Duration::ZERO` if `time1` is less than or equal to `time2`.
fn subtract_abs(time1: Duration, time2: Duration) -> Duration {
    if time1 > time2 {
        time1 - time2
    } else {
        Duration::ZERO
    }
}
