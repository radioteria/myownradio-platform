use crate::transcoder::Stats;
use crate::{utils, AudioTranscoder, OutputFormat, TranscoderCreationError, TranscodingError};
use std::sync::{Arc, Mutex};
use std::time::Duration;

/// An asynchronous wrapper around a synchronous audio transcoder.
///
/// This struct provides an asynchronous interface for working with the `AudioTranscoder`.
/// It allows receiving transcoded packets asynchronously using async/await syntax.
pub struct AudioTranscoderAsync {
    transcoder: Arc<Mutex<AudioTranscoder>>,
}

impl AudioTranscoderAsync {
    /// Creates a new `AsyncAudioTranscoder` instance.
    ///
    /// # Arguments
    ///
    /// * `source_url` - The URL of the audio source.
    /// * `offset` - The offset duration for transcoding.
    /// * `output_format` - The desired output format.
    ///
    /// # Errors
    ///
    /// Returns a `TranscoderCreationError` if the creation of the underlying `AudioTranscoder` fails.
    pub fn create(
        source_url: &str,
        offset: &Duration,
        output_format: &OutputFormat,
    ) -> Result<Self, TranscoderCreationError> {
        let transcoder = Arc::new(Mutex::new(AudioTranscoder::create(
            source_url,
            offset,
            output_format,
        )?));

        Ok(Self { transcoder })
    }

    /// Returns the statistics of the underlying `AudioTranscoder`.
    pub fn stats(&self) -> Stats {
        self.transcoder.lock().unwrap().stats().clone()
    }

    /// Receives the next set of transcoded packets asynchronously.
    ///
    /// This method returns a `Result` that resolves to an optional vector of `Packet`s.
    /// If successful, it returns `Some` containing the transcoded packets.
    /// If there are no more packets available, it returns `Ok(None)`.
    pub async fn receive_next_transcoded_packets(
        &mut self,
    ) -> Result<Option<Vec<utils::Packet>>, TranscodingError> {
        let transcoder = self.transcoder.clone();

        actix_rt::task::spawn_blocking(move || {
            transcoder.lock().unwrap().receive_next_transcoded_packets()
        })
        .await
        .expect("Unable to spawn blocking task")
    }
}

#[cfg(test)]
mod tests {
    extern crate ffmpeg_next as ffmpeg;

    use crate::transcoder_async::{AudioTranscoderAsync, OutputFormat};
    use std::time::Duration;

    #[ctor::ctor]
    fn init() {
        ffmpeg::init().expect("Unable to initialize FFmpeg");
    }

    #[actix_rt::test]
    #[tracing_test::traced_test]
    async fn test_transcoding() {
        let test_file = "tests/fixtures/test_file.wav";
        let test_cases = vec![
            (
                OutputFormat::MP3 {
                    bit_rate: 128_000,
                    sampling_rate: 48_000,
                },
                427,
                489647,
            ),
            (
                OutputFormat::AAC {
                    bit_rate: 64_000,
                    sampling_rate: 48_000,
                },
                481,
                489472,
            ),
        ];
        let offset = Duration::from_millis(0);

        for (format, expected_packets, expected_last_pts) in test_cases {
            let mut actual_packets = 0;
            let mut actual_last_pts = 0;

            let mut transcoder = AudioTranscoderAsync::create(test_file, &offset, &format).unwrap();

            while let Ok(Some(packets)) = transcoder.receive_next_transcoded_packets().await {
                actual_packets += packets.len();
                actual_last_pts = packets.last().map(|p| p.pts().value()).unwrap_or_default()
            }

            assert_eq!(expected_packets, actual_packets);
            assert_eq!(expected_last_pts, actual_last_pts);
        }
    }
}
