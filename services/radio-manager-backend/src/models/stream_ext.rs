use crate::models::stream::{Stream, StreamStatus};

pub(crate) enum TimeOffsetComputationError {
    UnexpectedStreamState,
    UnknownStreamStatus,
    StreamStopped,
}

pub(crate) trait TimeOffsetWithOverflow {
    fn calculate_time_offset(
        &self,
        timestamp: &i64,
        tracks_duration: &i64,
    ) -> Result<i64, TimeOffsetComputationError>;
}

impl TimeOffsetWithOverflow for Stream {
    fn calculate_time_offset(
        &self,
        timestamp: &i64,
        tracks_duration: &i64,
    ) -> Result<i64, TimeOffsetComputationError> {
        match (&self.status, &self.started, &self.started_from) {
            (&StreamStatus::Playing, Some(started), Some(started_from)) => {
                Ok(((timestamp - started) + started_from) % tracks_duration)
            }
            (&StreamStatus::Playing, _, _) => {
                Err(TimeOffsetComputationError::UnexpectedStreamState)
            }
            (&StreamStatus::Stopped, _, _) => Err(TimeOffsetComputationError::StreamStopped),
            (&StreamStatus::Unknown, _, _) => Err(TimeOffsetComputationError::UnknownStreamStatus),
        }
    }
}
