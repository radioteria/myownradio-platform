use crate::running_time::RunningTime;
use crate::types::{NowPlaying, NowPlayingClient, NowPlayingError};
use crate::utils::threshold_minimum;
use crate::CurrentTrack;
use myownradio_ffmpeg_utils::{
    AudioTranscoderAsync, OutputFormat, Packet, TranscoderCreationError, TranscodingError,
};
use std::fmt::Debug;
use std::time::{Duration, SystemTime};
use tracing::{debug, error, trace, warn};

const MAX_TRANSCODING_ATTEMPTS: usize = 5;
const TRACK_POSITION_THRESHOLD: Duration = Duration::from_millis(150);

#[derive(Debug, thiserror::Error)]
pub enum PlayerLoopError {
    #[error(transparent)]
    NowPlayingError(#[from] NowPlayingError),
    #[error(transparent)]
    TranscoderCreationError(#[from] TranscoderCreationError),
    #[error(transparent)]
    TranscodingError(#[from] TranscodingError),
}

pub struct PlayerLoop<C: NowPlayingClient> {
    channel_id: u64,
    api_client: C,
    transcoder: Option<AudioTranscoderAsync>,
    output_format: OutputFormat,
    running_time: RunningTime,
    initial_time: SystemTime,
    current_track: Option<CurrentTrack>,
    transcoding_attempts: usize,
}

impl<C: NowPlayingClient> PlayerLoop<C> {
    pub fn create(
        channel_id: u64,
        api_client: C,
        output_format: OutputFormat,
        initial_time: SystemTime,
    ) -> Result<Self, PlayerLoopError> {
        let running_time = RunningTime::new();
        let transcoder = None;
        let current_track = None;
        let transcoding_attempts = 0;

        Ok(Self {
            channel_id,
            api_client,
            transcoder,
            output_format,
            running_time,
            initial_time,
            current_track,
            transcoding_attempts,
        })
    }

    /// Receives the next set of audio packets from the current transcoder.
    ///
    /// If there is no current transcoder, fetches now playing information from the API
    /// and creates a new transcoder for the selected stream.
    ///
    /// If a transcoder is currently active but has run out of packets, it is closed and
    /// the loop will move on to the next track.
    ///
    /// # Returns
    ///
    /// Returns a `Result` containing the vector of received packets or an error.
    // @todo Rename to `process_next_audio_packets`
    pub async fn receive_next_audio_packets(&mut self) -> Result<Vec<Packet>, PlayerLoopError> {
        loop {
            if let Some(transcoder) = &mut self.transcoder {
                // @todo Rename to `fetch_next_transcoded_packets`
                match transcoder.receive_next_transcoded_packets().await {
                    Ok(Some(mut packets)) => {
                        trace!("Received {} packets from transcoder", packets.len());
                        self.update_packet_timestamps(&mut packets);

                        return Ok(packets);
                    }
                    Ok(None) => {
                        trace!("Received EOF from transcoder");
                        let stats = transcoder.stats();

                        // Adjust running time for the last packet's duration or remaining track duration,
                        // or for 50ms in worth case to prevent stuck on frozen time.
                        let adv_duration =
                            match (&stats.last_output_packet_duration, &self.current_track) {
                                (Some(last_packet_duration), _) => last_packet_duration.into(),
                                (None, Some(current_track)) => current_track.remaining_duration(),
                                _ => Duration::from_millis(50),
                            };
                        debug!(
                            "Advancing running time after transcoding complete by {:?}",
                            adv_duration
                        );
                        self.running_time.advance_by_duration(&adv_duration);

                        // If current transcoder has no more packets, close it and
                        // prepare for fetching the next track.
                        debug!("Destroying completed transcoder");

                        self.transcoder.take();
                    }
                    Err(error) if self.transcoding_attempts >= MAX_TRANSCODING_ATTEMPTS => {
                        return Err(error.into());
                    }
                    Err(error) => {
                        self.transcoding_attempts += 1;

                        warn!(
                            ?error,
                            "An error occurred while performing transcoding. Retry attempt {} of {}",
                            self.transcoding_attempts,
                            MAX_TRANSCODING_ATTEMPTS
                        );

                        self.running_time
                            .advance_by_duration(&Duration::from_millis(25));

                        self.restart();

                        continue;
                    }
                }
            }

            // If there is no current transcoder, fetch now playing information
            // for the current channel and create a new transcoder for the new
            // track and output format.
            let clock_time = self.initial_time + *self.running_time.time();
            debug!(?clock_time, "Fetching now playing object");

            let now_playing = self
                .api_client
                .get_now_playing(&self.channel_id, &clock_time)
                .await?;
            let current_track = self
                .current_track
                .insert(Self::get_current_track(now_playing));

            self.running_time.reset_pts();

            let transcoder = AudioTranscoderAsync::create(
                &current_track.url,
                &current_track.position,
                &self.output_format,
            )
            .await?;

            self.transcoder.replace(transcoder);
        }
    }

    /// Restarts the player loop by resetting the running time and clearing the transcoder.
    pub fn restart(&mut self) {
        debug!("Restarting player loop");
        self.running_time.reset_pts();
        self.transcoder.take();
    }

    /// Get the title of the track that is being decoded.
    pub fn current_title(&self) -> Option<&str> {
        self.current_track
            .as_ref()
            .map(|track| track.title.as_str())
    }

    /// Get the current running time value.
    pub fn current_running_time(&self) -> &Duration {
        self.running_time.time()
    }

    /// Updates the PTS values of a set of audio packets using the running time.
    ///
    /// The PTS is used to synchronize the packets with the audio player's timeline.
    ///
    /// This function advances the running time based on the PTS of the current packet,
    /// and updates the PTS value of the packet based on the running time.
    fn update_packet_timestamps(&mut self, packets: &mut Vec<Packet>) {
        for packet in packets {
            // Update the running time based on the PTS value of the current packet.
            self.running_time
                .advance_by_next_pts(&packet.pts_as_duration());
            // Update the PTS value of the packet based on the running time.
            packet.set_pts((*self.running_time.time()).into())
        }
    }

    fn get_current_track(now_playing: NowPlaying) -> CurrentTrack {
        if now_playing.current.position <= TRACK_POSITION_THRESHOLD {
            return CurrentTrack {
                position: Duration::ZERO,
                ..now_playing.current
            };
        }

        if now_playing.current.remaining_duration() <= TRACK_POSITION_THRESHOLD {
            return CurrentTrack {
                url: now_playing.next.url,
                title: now_playing.next.title,
                position: Duration::ZERO,
                duration: now_playing.next.duration,
            };
        }

        now_playing.current
    }
}

#[cfg(test)]
mod tests {
    use super::*;
    use crate::types::{CurrentTrack, NextTrack};
    use crate::{NowPlayingError, PlayerLoop, PlayerLoopError};
    use myownradio_ffmpeg_utils::{OutputFormat, Packet, Timestamp};
    use std::sync::{Arc, Mutex};
    use std::time::{Duration, SystemTime};

    #[derive(Clone)]
    struct MockAPIClient {
        calls: Arc<Mutex<Vec<(u32, SystemTime)>>>,
    }

    impl MockAPIClient {
        fn new() -> Self {
            Self {
                calls: Arc::new(Mutex::new(vec![])),
            }
        }
    }

    #[trait_async::trait_async]
    impl NowPlayingClient for MockAPIClient {
        async fn get_now_playing(
            &self,
            channel_id: &u32,
            time: &SystemTime,
        ) -> Result<NowPlaying, NowPlayingError> {
            let timeline_position_micros = time
                .duration_since(SystemTime::UNIX_EPOCH)
                .unwrap()
                .as_micros();

            let duration = Duration::from_secs_f32(6.426122);
            let duration_micros = 6426122;
            let track_position_micros = timeline_position_micros % duration_micros;
            let position = Duration::from_micros(track_position_micros as u64);

            self.calls.lock().unwrap().push((*channel_id, *time));

            Ok(NowPlaying {
                current: CurrentTrack {
                    title: String::from("Sample Track"),
                    url: String::from("tests/fixtures/sample-6s.mp3"),
                    duration,
                    position,
                },
                next: NextTrack {
                    title: String::from("Sample Track"),
                    url: String::from("tests/fixtures/sample-6s.mp3"),
                    duration,
                },
            })
        }
    }

    #[actix_rt::test]
    async fn test_create_player_loop() {
        let api_client = MockAPIClient::new();
        let output_format = OutputFormat::MP3 {
            bit_rate: 128_000,
            sampling_rate: 48_000,
        };
        let initial_time = SystemTime::UNIX_EPOCH;
        let result = PlayerLoop::create(123, api_client, output_format, initial_time);

        assert!(result.is_ok());
    }

    #[actix_rt::test]
    async fn test_receive_track_title() {
        let api_client = MockAPIClient::new();
        let output_format = OutputFormat::MP3 {
            bit_rate: 128_000,
            sampling_rate: 48_000,
        };
        let initial_time = SystemTime::UNIX_EPOCH;
        let mut player_loop =
            PlayerLoop::create(123, api_client, output_format, initial_time).unwrap();

        assert!(player_loop.current_title().is_none());
        assert!(player_loop.receive_next_audio_packets().await.is_ok());
        assert!(player_loop.current_title().is_some());
        assert_eq!("Sample Track", player_loop.current_title().unwrap());
    }

    #[actix_rt::test]
    async fn test_restart_player_loop() {
        // Create a mock API client.
        let api_client = MockAPIClient::new();

        // Define the output format, initial time, and channel ID.
        let output_format = OutputFormat::MP3 {
            bit_rate: 128_000,
            sampling_rate: 48_000,
        };
        let initial_time = SystemTime::UNIX_EPOCH;
        let channel_id = 123;

        // Create a new player loop with the mock API client and other parameters.
        let mut player_loop =
            PlayerLoop::create(channel_id, api_client.clone(), output_format, initial_time)
                .unwrap();

        // Check that the API client hasn't been called yet.
        assert_eq!(0, api_client.calls.lock().unwrap().len());

        // Fetch the next set of audio packets from the player loop.
        assert!(player_loop.receive_next_audio_packets().await.is_ok());

        // Check that the API client was called once.
        assert_eq!(1, api_client.calls.lock().unwrap().len());

        // Skip ahead in the current track by 500 milliseconds.
        skip_packets(&mut player_loop, &Duration::from_millis(500)).await;

        // Restart the player loop.
        player_loop.restart();

        // Skip ahead in the next track by 500 milliseconds.
        skip_packets(&mut player_loop, &Duration::from_millis(500)).await;

        // Check that the API client was called again after the player loop was restarted.
        assert_eq!(2, api_client.calls.lock().unwrap().len());

        // Check that the API client was called with the correct channel ID and time arguments.
        assert_eq!(
            (channel_id, initial_time),
            api_client.calls.lock().unwrap()[0]
        );
        assert_eq!(
            (channel_id, initial_time + Duration::from_nanos(529062500)),
            api_client.calls.lock().unwrap()[1]
        );
    }

    async fn skip_packets(player_loop: &mut PlayerLoop<MockAPIClient>, amount: &Duration) {
        let current_time = *player_loop.current_running_time();

        while *player_loop.current_running_time() - current_time < *amount {
            player_loop.receive_next_audio_packets().await.unwrap();
        }
    }
}
