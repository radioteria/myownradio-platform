use serde::Deserialize;
use slog::Level;
use std::process::{Command, Output};

#[derive(Copy, Clone, Debug, Deserialize, PartialEq)]
#[serde(field_identifier, remote = "Level", untagged)]
pub enum LogLevel {
    Critical,
    Error,
    Warning,
    Info,
    Debug,
    Trace,
}

fn default_bind_address() -> String {
    "127.0.0.1:8080".to_string()
}

fn default_log_level() -> Level {
    Level::Error
}

fn default_path_to_ffprobe() -> String {
    let Output { stdout, status, .. } = Command::new("which").args(&["ffprobe"]).output().unwrap();

    if !status.success() {
        panic!("Unable to locate ffprobe")
    }

    String::from_utf8(stdout).unwrap().trim().to_string()
}

fn default_path_to_ffmpeg() -> String {
    let Output { stdout, status, .. } = Command::new("which").args(&["ffmpeg"]).output().unwrap();

    if !status.success() {
        panic!("Unable to locate ffprobe")
    }

    String::from_utf8(stdout).unwrap().trim().to_string()
}

#[derive(Clone, Debug, Deserialize)]
pub struct Config {
    #[serde(default = "default_bind_address")]
    pub bind_address: String,
    #[serde(default = "default_path_to_ffmpeg")]
    pub path_to_ffmpeg: String,
    #[serde(default = "default_path_to_ffprobe")]
    pub path_to_ffprobe: String,
    #[serde(with = "LogLevel", default = "default_log_level")]
    pub log_level: Level,
    // Required environment variables
    pub mor_backend_url: String,
}

impl Config {
    pub fn from_env() -> Self {
        match envy::from_env::<Self>() {
            Ok(config) => config,
            Err(error) => panic!("Missing environment variable: {:#?}", error),
        }
    }
}
