use std::process::{Command, Output};
use std::time::{Duration, SystemTime, UNIX_EPOCH};

pub fn which(command: &str) -> Option<String> {
    let Output { stdout, status, .. } = Command::new("which").args(&[command]).output().unwrap();

    if !status.success() {
        return None;
    }

    Some(String::from_utf8(stdout).unwrap().trim().to_string())
}

pub(crate) fn now() -> i64 {
    SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .unwrap()
        .as_millis() as i64
}

pub(crate) fn now_duration() -> Duration {
    SystemTime::now().duration_since(UNIX_EPOCH).unwrap()
}
