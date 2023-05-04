use crate::running_time::RunningTime;
use myownradio_ffmpeg_utils::{
    AudioTranscoder, AudioTranscoderCreationError, OutputFormat, Packet, TranscodeError,
};
use std::fmt::Debug;
use std::time::{Duration, SystemTime};

/// Defines an error that occurred while fetching now playing information.
pub trait NowPlayingError: Debug {
    fn get_code(&self) -> usize;
    fn get_message(&self) -> String;
}

/// Defines the response of the now playing API.
pub trait NowPlayingResponse {
    fn get_url(&self) -> String;
    fn get_title(&self) -> String;
    fn get_duration(&self) -> Duration;
    fn get_position(&self) -> Duration;
}

/// Defines the interface for the now playing API client.
pub trait NowPlayingAPIClient {
    fn get_now_playing(
        &self,
        channel_id: &u32,
        time: &SystemTime,
    ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>>;
}

/// Defines the possible errors that can occur during player loop operations.
#[derive(Debug)]
pub enum PlayerLoopError {
    NowPlayingError(Box<dyn NowPlayingError>),
    AudioTranscoderCreationError(AudioTranscoderCreationError),
    TranscodeError(TranscodeError),
}

/// Implements the main player loop functionality.
pub struct PlayerLoop<API> {
    channel_id: u32,
    api_client: API,
    transcoder: Option<AudioTranscoder>,
    output_format: OutputFormat,
    running_time: RunningTime,
    initial_time: SystemTime,
    current_title: Option<String>,
}

impl<API: NowPlayingAPIClient> PlayerLoop<API> {
    /// Creates a new instance of the `PlayerLoop` struct.
    pub fn create(
        channel_id: u32,
        api_client: API,
        output_format: OutputFormat,
        initial_time: SystemTime,
    ) -> Result<Self, PlayerLoopError>
    where
        API: NowPlayingAPIClient,
    {
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

        if self.transcoder.is_none() {
            // If there is no current transcoder, fetch now playing information
            // for the current channel and create a new transcoder for the new
            // track and output format.
            let player_time = self.initial_time + *self.running_time.time();
            let now_playing = self
                .api_client
                .get_now_playing(&self.channel_id, &player_time)
                .map_err(|error| PlayerLoopError::NowPlayingError(error))?;

            let transcoder = AudioTranscoder::create(
                &now_playing.get_url(),
                &now_playing.get_position(),
                &self.output_format,
            )
            .map_err(|error| PlayerLoopError::AudioTranscoderCreationError(error))?;

            self.running_time.reset();
            self.transcoder.replace(transcoder);
        }

        // If there is no current transcoder, return an empty vector of packets.
        Ok(vec![])
    }

    /// Restarts the player loop by resetting the running time and clearing the transcoder.
    pub fn restart(&mut self) {
        self.running_time.reset();
        self.transcoder.take();
    }

    /// Get the title of the track that is being decoded.
    pub fn current_title(&self) -> Option<&String> {
        self.current_title.as_ref()
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
            self.running_time.advance(&packet.pts_as_duration());
            // Update the PTS value of the packet based on the running time.
            packet.set_pts((*self.running_time.time()).into())
        }
    }
}

#[cfg(test)]
mod tests {
    use super::NowPlayingAPIClient;
    use crate::{NowPlayingError, NowPlayingResponse, PlayerLoop, PlayerLoopError};
    use myownradio_ffmpeg_utils::{OutputFormat, Packet};
    use std::time::{Duration, SystemTime};

    struct TestClientResponse {
        position: Duration,
    }

    impl NowPlayingResponse for TestClientResponse {
        fn get_url(&self) -> String {
            String::from("tests/fixtures/sample-6s.mp3")
        }

        fn get_title(&self) -> String {
            String::from("Sample Track")
        }

        fn get_duration(&self) -> Duration {
            Duration::from_secs_f32(6.426122)
        }

        fn get_position(&self) -> Duration {
            self.position
        }
    }

    #[derive(Debug)]
    struct TestClientError {}

    impl NowPlayingError for TestClientError {
        fn get_code(&self) -> usize {
            todo!()
        }

        fn get_message(&self) -> String {
            todo!()
        }
    }

    struct TestClient {}

    static START_SEEK_TOLERANCE_MS: u128 = 250000;

    impl NowPlayingAPIClient for TestClient {
        fn get_now_playing(
            &self,
            _channel_id: &u32,
            time: &SystemTime,
        ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>> {
            let duration = 6426122;
            let mut position = time
                .duration_since(SystemTime::UNIX_EPOCH)
                .unwrap()
                .as_micros()
                % duration;

            if position < START_SEEK_TOLERANCE_MS || duration - position < START_SEEK_TOLERANCE_MS {
                position = 0
            }

            let position = Duration::from_micros(position as u64);

            Ok(Box::new(TestClientResponse { position }))
        }
    }

    impl Iterator for PlayerLoop<TestClient> {
        type Item = Result<Vec<Packet>, PlayerLoopError>;

        fn next(&mut self) -> Option<Self::Item> {
            Some(self.receive_next_audio_packets())
        }
    }

    #[test]
    fn test_player_loop() {
        let api_client = TestClient {};
        let time = SystemTime::UNIX_EPOCH;
        let player_loop = PlayerLoop::create(
            0,
            api_client,
            OutputFormat::MP3 {
                bit_rate: 128_000,
                sampling_rate: 48_000,
            },
            time,
        )
        .expect("Unable to initialize PlayerLoop");
        let mut iter = player_loop.take(2048);

        let mut previous_pts = Duration::ZERO;
        while let Some(Ok(pkg)) = iter.next() {
            if pkg.is_empty() {
                continue;
            }

            let pts = pkg.first().unwrap().pts_as_duration();

            assert!(pts >= previous_pts);

            previous_pts = pts;
        }
    }
}
