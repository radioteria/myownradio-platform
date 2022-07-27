use std::process::{Command, Output};

pub fn which(command: &str) -> Option<String> {
    let Output { stdout, status, .. } = Command::new("which").args(&[command]).output().unwrap();

    if !status.success() {
        return None;
    }

    Some(String::from_utf8(stdout).unwrap().trim().to_string())
}
