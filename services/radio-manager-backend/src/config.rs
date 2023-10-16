use crate::system::which;
use serde::Deserialize;
use tracing::Level;

#[derive(Copy, Clone, Debug, Deserialize, PartialEq)]
#[serde(field_identifier, remote = "Level", untagged)]
pub(crate) enum LogLevel {
    ERROR,
    WARN,
    INFO,
    DEBUG,
    #[serde(rename = "Trace")]
    TRACE,
}

#[derive(Copy, Clone, Debug, Deserialize, PartialEq)]
#[serde(field_identifier, untagged)]
pub(crate) enum LogFormat {
    Json,
    Term,
}

fn default_bind_address() -> String {
    "0.0.0.0:8080".to_string()
}

fn default_log_level() -> Level {
    Level::DEBUG
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
pub(crate) struct MySqlConfig {
    #[serde(rename = "mysql_host")]
    pub(crate) host: String,
    #[serde(rename = "mysql_user")]
    pub(crate) user: String,
    #[serde(rename = "mysql_password")]
    pub(crate) password: String,
    #[serde(rename = "mysql_database")]
    pub(crate) database: String,
}

impl MySqlConfig {
    pub(crate) fn connection_string(&self) -> String {
        format!(
            "mysql://{}:{}@{}/{}",
            self.user, self.password, self.host, self.database
        )
    }
}

#[derive(Clone, Debug, Deserialize)]
pub(crate) struct RadioStreamerConfig {
    #[serde(rename = "radio_streamer_endpoint")]
    pub(crate) endpoint: String,
    #[serde(rename = "radio_streamer_token")]
    pub(crate) token: String,
}

#[derive(Clone, Debug, Deserialize)]
pub(crate) struct PubsubBackendConfig {
    #[serde(rename = "pubsub_backend_endpoint")]
    pub(crate) endpoint: String,
}

#[derive(Clone, Debug, Deserialize)]
pub(crate) struct Config {
    #[serde(default = "default_bind_address")]
    pub(crate) bind_address: String,
    #[serde(default = "default_path_to_ffmpeg")]
    pub(crate) path_to_ffmpeg: String,
    #[serde(default = "default_path_to_ffprobe")]
    pub(crate) path_to_ffprobe: String,
    #[serde(with = "LogLevel", default = "default_log_level")]
    pub(crate) log_level: Level,
    #[serde(default = "default_log_format")]
    pub(crate) log_format: LogFormat,
    #[serde(default = "default_shutdown_timeout")]
    pub(crate) shutdown_timeout: u64,
    #[serde(flatten)]
    pub(crate) mysql: MySqlConfig,
    #[serde(flatten)]
    pub(crate) radio_streamer: RadioStreamerConfig,
    #[serde(flatten)]
    pub(crate) pubsub: PubsubBackendConfig,
    pub(crate) file_server_endpoint: String,
    pub(crate) file_system_root_path: String,
    pub(crate) auth_jwt_secret_key: String,
}

impl Config {
    pub(crate) fn from_env() -> Self {
        match envy::from_env::<Self>() {
            Ok(config) => config,
            Err(error) => panic!("Missing environment variable: {:#?}", error),
        }
    }
}
