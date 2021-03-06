use actix_web::client::{Client, SendRequestError};
use actix_web::http::StatusCode;
use serde::{Deserialize, Serialize};
use slog::{error, Logger};

#[derive(Deserialize, Debug, Serialize)]
pub struct CurrentTrack {
    pub offset: u32,
    pub title: String,
    pub url: String,
}

#[derive(Deserialize, Debug, Serialize)]
pub struct NextTrack {
    pub title: String,
    pub url: String,
}

#[derive(Deserialize, Debug, Serialize)]
pub struct NowPlaying {
    pub time: f32,
    pub playlist_position: u32,
    pub current_track: CurrentTrack,
    pub next_track: NextTrack,
}

#[derive(Deserialize, Debug)]
pub struct NowPlayingResponse {
    pub code: u8,
    pub message: String,
    pub data: NowPlaying,
}

pub struct MorBackendClient {
    logger: Logger,
    mor_backend_url: String,
}

pub enum MorBackendClientError {
    SendRequestError,
    UnexpectedStatusCode,
    ResponseReadError,
    ResponseParseError,
}

impl MorBackendClient {
    pub fn new(mor_backend_url: &str, logger: &Logger) -> Self {
        Self {
            logger: logger.clone(),
            mor_backend_url: mor_backend_url.to_string(),
        }
    }

    pub async fn get_now_playing(
        &self,
        channel_id: &u32,
    ) -> Result<NowPlaying, MorBackendClientError> {
        let client = Client::default();

        let url = format!("{}/api/v1/stream/{}/now", &self.mor_backend_url, channel_id);

        let mut response = match client.get(url).send().await {
            Ok(response) => response,
            Err(error) => {
                error!(self.logger, "Unable to send request"; "error" => ?error);
                return Err(MorBackendClientError::SendRequestError);
            }
        };

        let body = match response.status() {
            StatusCode::OK => response.body().await,
            status_code => {
                error!(self.logger, "Unexpected status code"; "status_code" => ?status_code);
                return Err(MorBackendClientError::UnexpectedStatusCode);
            }
        };

        let bytes = match body {
            Ok(bytes) => bytes,
            Err(error) => {
                error!(self.logger, "Unable to read response"; "error" => ?error);
                return Err(MorBackendClientError::ResponseReadError);
            }
        };

        match serde_json::from_slice::<NowPlayingResponse>(&bytes) {
            Ok(now_playing_response) => Ok(now_playing_response.data),
            Err(error) => {
                error!(self.logger, "Unable to parse response"; "error" => ?error);
                Err(MorBackendClientError::ResponseParseError)
            }
        }
    }
}
