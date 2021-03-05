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

#[derive(Clone, Debug, Deserialize)]
pub struct Config {
    #[serde(default = "default_bind_address")]
    pub bind_address: String,
    #[serde(with = "LogLevel", default = "default_log_level")]
    pub log_level: Level,
    pub mor_backend_url: String,
}

fn default_bind_address() -> String {
    "127.0.0.1:8080".to_string()
}

fn default_log_level() -> Level {
    Level::Error
}

impl Config {
    pub fn from_env() -> Self {
        match envy::from_env::<Self>() {
            Ok(config) => config,
            Err(error) => panic!("Missing environment variable: {:#?}", error),
        }
    }
}
