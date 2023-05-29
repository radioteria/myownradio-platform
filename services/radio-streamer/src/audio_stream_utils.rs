use crate::backend_client::BackendClient;
use myownradio_player_loop::{NowPlaying, NowPlayingClient, NowPlayingError, PlayerLoop};
use std::time::SystemTime;
use tracing::error;

#[async_trait::async_trait]
impl NowPlayingClient for BackendClient {
    async fn get_now_playing(
        &self,
        channel_id: &u64,
        time: &SystemTime,
    ) -> Result<NowPlaying, NowPlayingError> {
        let channel_id = *channel_id as usize;

        match BackendClient::get_now_playing(self, &channel_id, time).await {
            Ok(now_playing) => Ok(myownradio_player_loop::NowPlaying {
                current: myownradio_player_loop::CurrentTrack {
                    url: now_playing.current_track.url,
                    title: now_playing.current_track.title,
                    position: now_playing.current_track.offset,
                    duration: now_playing.current_track.duration,
                },
                next: myownradio_player_loop::NextTrack {
                    url: now_playing.next_track.url,
                    title: now_playing.next_track.title,
                    duration: now_playing.next_track.duration,
                },
            }),
            Err(error) => {
                error!(?error, "Error happened on getting NowPlaying object");
                Err(NowPlayingError::NonRetryable)
            }
        }
    }
}
