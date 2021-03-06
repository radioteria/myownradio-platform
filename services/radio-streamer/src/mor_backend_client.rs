use actix_web::client::{Client, SendRequestError};
use actix_web::http::StatusCode;

struct CurrentTrack {
    pub offset: u32,
    pub title: String,
    pub url: String,
}

struct NextTrack {
    pub title: String,
    pub url: String,
}

pub struct NowPlaying {
    pub time: f32,
    pub playlist_position: u32,
    pub current_track: CurrentTrack,
    pub next_track: NextTrack,
}

pub struct MorBackendClient {
    mor_backend_url: String,
    client: Client,
}

pub enum MorBackendClientError {
    Other,
}

impl MorBackendClient {
    pub fn new(mor_backend_url: String) -> Self {
        let client = Client::default();

        Self {
            mor_backend_url,
            client,
        }
    }

    pub async fn get_now_playing(
        &self,
        channel_id: &i32,
    ) -> Result<NowPlaying, MorBackendClientError> {
        let url = format!("{}/api/v1/stream/{}/now", &self.mor_backend_url, channel_id);

        match self.client.get(url).send().await {
            Ok(mut response) => {
                let status = response.status();
                match status {
                    StatusCode::OK => {
                        let maybe_now_playing = match response.body().await {
                            Ok(bytes) => serde_json::from_slice::<NowPlaying>(&bytes);
                            Err(_error) => Err(MorBackendClientError::Other)
                        }
                        match maybe_now_playing {
                            Ok(now_playing) => Ok(now_playing),
                            Err(_error) => Err(MorBackendClientError::Other),
                        }
                    }
                    _ => Err(MorBackendClientError::Other)
                }
            }
            Err(_error) => Err(MorBackendClientError::Other),
        }

    }
}
