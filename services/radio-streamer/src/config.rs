use crate::helpers::system::which;
use serde::Deserialize;
use slog::Level;

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
    "0.0.0.0:8080".to_string()
}

fn default_log_level() -> Level {
    Level::Warning
}

fn default_path_to_ffprobe() -> String {
    match which("ffprobe") {
        Some(path) => path,
        None => {
            panic!("Unable to locate ffprobe");
        }
    }
}

fn default_path_to_ffmpeg() -> String {
    match which("ffmpeg") {
        Some(path) => path,
        None => {
            panic!("Unable to locate ffprobe");
        }
    }
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
    pub stream_mutation_token: String,
}

impl Config {
    pub fn from_env() -> Self {
        match envy::from_env::<Self>() {
            Ok(config) => config,
            Err(error) => panic!("Missing environment variable: {:#?}", error),
        }
    }
}
