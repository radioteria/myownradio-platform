use crate::{Frame, Timestamp, INTERNAL_SAMPLING_FREQUENCY, RESAMPLER_TIME_BASE};
use futures::channel::mpsc::{channel, Receiver};
use futures::SinkExt;
use iter_tools::Itertools;
use std::mem::size_of;
use std::time::Duration;
use tracing::{debug, trace};

const SILENCE_FRAME_SIZE: usize = 1024;

#[tracing::instrument]
pub fn generate_silence(duration: Option<&Duration>) -> Receiver<Frame> {
    debug!(?duration, "Initializing silence generator");

    let (mut sender, receiver) = channel(0);

    let num_samples = match duration {
        Some(duration) => {
            let dur_millis = duration.as_secs_f64();
            (dur_millis * INTERNAL_SAMPLING_FREQUENCY as f64) as i32
        }
        None => i32::MAX,
    };

    debug!(?duration, "Generating {} samples of silence", num_samples);

    let frames = (0..num_samples)
        .map(|_| 0i32.to_le_bytes())
        .chunks(SILENCE_FRAME_SIZE);

    actix_rt::spawn(async move {
        for (frame_number, frame_samples) in frames.into_iter().enumerate() {
            let data = frame_samples.flatten().collect::<Vec<_>>();

            let frame_size = data.len() / size_of::<i32>();

            let pts = Timestamp::new(
                frame_number as i64 * SILENCE_FRAME_SIZE as i64,
                RESAMPLER_TIME_BASE,
            );
            let duration = Timestamp::new(frame_size as i64, RESAMPLER_TIME_BASE);

            trace!(?pts, ?duration, "Generating silent frame");

            let buffer = Frame::new(pts, duration, data);

            if let Err(_) = sender.send(buffer).await {
                return;
            }
        }
    });

    receiver
}

#[cfg(test)]
mod tests {
    use super::generate_silence;
    use futures::StreamExt;
    use std::time::Duration;

    #[actix_rt::test]
    #[tracing_test::traced_test]
    async fn test_generating_5_seconds_of_silence() {
        let mut silence = generate_silence(Some(&Duration::from_secs(5)));

        let mut total_frames = 0;
        let mut min_timestamp = None::<Duration>;
        let mut duration = None::<Duration>;

        while let Some(frame) = silence.next().await {
            let pts: Duration = frame.pts().into();
            let dur: Duration = frame.duration().into();

            total_frames += 1;
            min_timestamp.get_or_insert(pts);
            duration = Some(pts + dur);
        }

        assert_eq!(Some(Duration::from_secs(0)), min_timestamp);
        assert_eq!(Some(Duration::from_secs(5)), duration);
        assert_eq!(235, total_frames);
    }
}
