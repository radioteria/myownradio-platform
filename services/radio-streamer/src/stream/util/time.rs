use std::time::Duration;

pub(crate) fn subtract_abs(time1: Duration, time2: Duration) -> Duration {
    if time1 > time2 {
        time1 - time2
    } else {
        Duration::ZERO
    }
}
