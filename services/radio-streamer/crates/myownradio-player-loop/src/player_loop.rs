use crate::running_time::RunningTime;
use crate::PlayerLoopIter;
use myownradio_ffmpeg_utils::{
    AudioTranscoder, AudioTranscoderCreationError, OutputFormat, Packet, TranscodeError,
};
use std::fmt::Debug;
use std::time::{Duration, SystemTime};

pub trait NowPlayingError: Debug + Send {}

pub trait NowPlayingResponse {
    fn curr_url(&self) -> String;
    fn curr_title(&self) -> String;
    fn curr_duration(&self) -> Duration;
    fn curr_position(&self) -> Duration;
    fn next_url(&self) -> String;
    fn next_title(&self) -> String;
    fn next_duration(&self) -> Duration;
}

pub trait NowPlayingClient {
    fn get_now_playing(
        &self,
        channel_id: &u32,
        time: &SystemTime,
    ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>>;
}

#[derive(Debug)]
pub enum PlayerLoopError {
    NowPlayingError(Box<dyn NowPlayingError>),
    AudioTranscoderCreationError(AudioTranscoderCreationError),
    TranscodeError(TranscodeError),
}

pub struct PlayerLoop<C: NowPlayingClient> {
    channel_id: u32,
    api_client: C,
    transcoder: Option<AudioTranscoder>,
    output_format: OutputFormat,
    running_time: RunningTime,
    initial_time: SystemTime,
    current_title: Option<String>,
}

impl<C: NowPlayingClient> PlayerLoop<C> {
    pub fn create(
        channel_id: u32,
        api_client: C,
        output_format: OutputFormat,
        initial_time: SystemTime,
    ) -> Result<Self, PlayerLoopError> {
        let running_time = RunningTime::new();
        let transcoder = None;
        let current_title = None;

        Ok(Self {
            channel_id,
            api_client,
            transcoder,
            output_format,
            running_time,
            initial_time,
            current_title,
        })
    }

    /// Receives the next set of audio packets from the current transcoder.
    ///
    /// If there is no current transcoder, fetches now playing information from the API
    /// and creates a new transcoder for the selected stream.
    ///
    /// If a transcoder is currently active but has run out of packets, it is closed and
    /// the loop will move on to the next track.
    pub fn receive_next_audio_packets(&mut self) -> Result<Vec<Packet>, PlayerLoopError> {
        if let Some(transcoder) = &mut self.transcoder {
            match transcoder
                .receive_next_transcoded_packets()
                .map_err(|error| PlayerLoopError::TranscodeError(error))?
            {
                Some(mut packets) => {
                    self.update_packets_pts(&mut packets);
                    return Ok(packets);
                }
                None => {
                    // If the current transcoder has no more packets, close it and
                    // prepare to fetch the next track.
                    self.transcoder.take();
                }
            }
        }

        // If there is no current transcoder, fetch now playing information
        // for the current channel and create a new transcoder for the new
        // track and output format.
        let player_time = self.initial_time + *self.running_time.time();
        let now_playing = self
            .api_client
            .get_now_playing(&self.channel_id, &player_time)
            .map_err(|error| PlayerLoopError::NowPlayingError(error))?;
        self.current_title = Some(now_playing.curr_title());

        self.running_time.reset_pts();
        let transcoder = AudioTranscoder::create(
            &now_playing.curr_url(),
            &now_playing.curr_position(),
            &self.output_format,
        )
        .map_err(|error| PlayerLoopError::AudioTranscoderCreationError(error))?;
        self.transcoder.replace(transcoder);

        self.receive_next_audio_packets()
    }

    /// Restarts the player loop by resetting the running time and clearing the transcoder.
    pub fn restart(&mut self) {
        self.running_time.reset_pts();
        self.transcoder.take();
    }

    /// Get the title of the track that is being decoded.
    pub fn current_title(&self) -> Option<&String> {
        self.current_title.as_ref()
    }

    /// Get the current running time value.
    pub fn current_running_time(&self) -> &Duration {
        self.running_time.time()
    }

    /// Converts the `PlayerLoop` instance into an iterator that yields `PlayerLoopEvent`s.
    ///
    /// The iterator allows processing events from the player loop, such as track title changes
    /// and received audio packets.
    pub fn into_iter(self) -> PlayerLoopIter<C> {
        PlayerLoopIter::new(self)
    }

    /// Updates the PTS values of a set of audio packets using the running time.
    ///
    /// The PTS is used to synchronize the packets with the audio player's timeline.
    ///
    /// This function advances the running time based on the PTS of the current packet,
    /// and updates the PTS value of the packet based on the running time.
    fn update_packets_pts(&mut self, packets: &mut Vec<Packet>) {
        for packet in packets {
            // Update the running time based on the PTS value of the current packet.
            self.running_time
                .advance_by_next_pts(&packet.pts_as_duration());
            // Update the PTS value of the packet based on the running time.
            packet.set_pts((*self.running_time.time()).into())
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;
    use crate::{
        NowPlayingError, NowPlayingResponse, PlayerLoop, PlayerLoopError, PlayerLoopEvent,
        PlayerLoopIter, Title,
    };
    use myownradio_ffmpeg_utils::{OutputFormat, Packet, Timestamp};
    use std::sync::{Arc, Mutex};
    use std::time::{Duration, SystemTime};

    struct MockClientResponse {
        position: Duration,
    }

    impl NowPlayingResponse for MockClientResponse {
        fn curr_url(&self) -> String {
            String::from("tests/fixtures/sample-6s.mp3")
        }

        fn curr_title(&self) -> String {
            String::from("Sample Track")
        }

        fn curr_duration(&self) -> Duration {
            Duration::from_secs_f32(6.426122)
        }

        fn curr_position(&self) -> Duration {
            self.position
        }

        fn next_url(&self) -> String {
            String::from("tests/fixtures/sample-6s.mp3")
        }

        fn next_title(&self) -> String {
            String::from("Sample Track")
        }

        fn next_duration(&self) -> Duration {
            Duration::from_secs_f32(6.426122)
        }
    }

    #[derive(Debug)]
    struct MockClientError;

    impl NowPlayingError for MockClientError {}

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

    impl NowPlayingClient for MockAPIClient {
        fn get_now_playing(
            &self,
            channel_id: &u32,
            time: &SystemTime,
        ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>> {
            let timeline_position_micros = time
                .duration_since(SystemTime::UNIX_EPOCH)
                .unwrap()
                .as_micros();

            let duration_micros = 6426122;
            let track_position_micros = timeline_position_micros % duration_micros;
            let position = Duration::from_micros(track_position_micros as u64);

            self.calls.lock().unwrap().push((*channel_id, *time));

            Ok(Box::new(MockClientResponse { position }))
        }
    }

    impl Iterator for PlayerLoop<MockAPIClient> {
        type Item = Result<Vec<Packet>, PlayerLoopError>;

        fn next(&mut self) -> Option<Self::Item> {
            Some(self.receive_next_audio_packets())
        }
    }

    #[test]
    fn test_create_player_loop() {
        let api_client = MockAPIClient::new();
        let output_format = OutputFormat::MP3 {
            bit_rate: 128_000,
            sampling_rate: 48_000,
        };
        let initial_time = SystemTime::UNIX_EPOCH;
        let result = PlayerLoop::create(123, api_client, output_format, initial_time);

        assert!(result.is_ok());
    }

    #[test]
    fn test_receive_track_title() {
        let api_client = MockAPIClient::new();
        let output_format = OutputFormat::MP3 {
            bit_rate: 128_000,
            sampling_rate: 48_000,
        };
        let initial_time = SystemTime::UNIX_EPOCH;
        let mut player_loop =
            PlayerLoop::create(123, api_client, output_format, initial_time).unwrap();

        assert!(player_loop.current_title().is_none());
        assert!(player_loop.receive_next_audio_packets().is_ok());
        assert!(player_loop.current_title().is_some());
        assert_eq!(
            "Sample Track",
            player_loop.current_title().unwrap().as_str()
        );
    }

    #[test]
    fn test_restart_player_loop() {
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
        assert!(player_loop.receive_next_audio_packets().is_ok());

        // Check that the API client was called once.
        assert_eq!(1, api_client.calls.lock().unwrap().len());

        // Skip ahead in the current track by 500 milliseconds.
        skip_packets(&mut player_loop, &Duration::from_millis(500));

        // Restart the player loop.
        player_loop.restart();

        // Skip ahead in the next track by 500 milliseconds.
        skip_packets(&mut player_loop, &Duration::from_millis(500));

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

    fn skip_packets(player_loop: &mut PlayerLoop<MockAPIClient>, amount: &Duration) {
        let current_time = *player_loop.current_running_time();

        while *player_loop.current_running_time() - current_time < *amount {
            player_loop.receive_next_audio_packets().unwrap();
        }
    }
}
