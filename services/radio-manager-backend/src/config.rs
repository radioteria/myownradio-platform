use crate::system::which;
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

#[derive(Copy, Clone, Debug, Deserialize, PartialEq)]
#[serde(field_identifier, untagged)]
pub enum LogFormat {
    Json,
    Term,
}

fn default_bind_address() -> String {
    "0.0.0.0:8080".to_string()
}

fn default_log_level() -> Level {
    Level::Debug
}

fn default_log_format() -> LogFormat {
    LogFormat::Json
}

fn default_shutdown_timeout() -> u64 {
    30u64
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
pub struct MySqlConfig {
    #[serde(rename = "mysql_host")]
    pub host: String,
    #[serde(rename = "mysql_user")]
    pub user: String,
    #[serde(rename = "mysql_password")]
    pub password: String,
    #[serde(rename = "mysql_database")]
    pub database: String,
}

impl MySqlConfig {
    pub fn connection_string(&self) -> String {
        format!(
            "mysql://{}:{}@{}/{}",
            self.user, self.password, self.host, self.database
        )
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
    #[serde(default = "default_log_format")]
    pub log_format: LogFormat,
    #[serde(default = "default_shutdown_timeout")]
    pub shutdown_timeout: u64,
    #[serde(flatten)]
    pub mysql: MySqlConfig,
}

impl Config {
    pub fn from_env() -> Self {
        match envy::from_env::<Self>() {
            Ok(config) => config,
            Err(error) => panic!("Missing environment variable: {:#?}", error),
        }
    }
}
