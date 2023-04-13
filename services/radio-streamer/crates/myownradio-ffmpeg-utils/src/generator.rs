use crate::{
    Frame, Timestamp, INTERNAL_SAMPLE_SIZE, INTERNAL_SAMPLING_FREQUENCY, RESAMPLER_TIME_BASE,
};
use futures::channel::mpsc::{channel, Receiver};
use futures::SinkExt;
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
            (dur_millis * INTERNAL_SAMPLING_FREQUENCY as f64) as i64
        }
        None => i64::MAX,
    };
    let num_full_frames = num_samples / SILENCE_FRAME_SIZE as i64;
    let num_samples_remainder = num_samples % SILENCE_FRAME_SIZE as i64;

    debug!("Going to produce {} frames of silence", num_full_frames);

    let frame_duration = Timestamp::new(SILENCE_FRAME_SIZE as i64, RESAMPLER_TIME_BASE);

    actix_rt::spawn(async move {
        for frame in 0..num_full_frames {
            let pts = Timestamp::new(frame * SILENCE_FRAME_SIZE as i64, RESAMPLER_TIME_BASE);
            let duration = frame_duration.clone();

            trace!(?pts, ?duration, ?frame, "Generating silent frame");

            let data = vec![0u8; SILENCE_FRAME_SIZE * INTERNAL_SAMPLE_SIZE];
            let buffer = Frame::new(pts, duration, data);

            if let Err(_) = sender.send(buffer).await {
                return;
            }
        }

        let frame = num_full_frames;
        let pts = Timestamp::new(frame * SILENCE_FRAME_SIZE as i64, RESAMPLER_TIME_BASE);
        let duration = Timestamp::new(num_samples_remainder as i64, RESAMPLER_TIME_BASE);

        trace!(?pts, ?duration, ?frame, "Generating last silent frame");

        let data = vec![0u8; num_samples_remainder as usize * INTERNAL_SAMPLE_SIZE];
        let buffer = Frame::new(pts, duration, data);

        let _ = sender.send(buffer).await;
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
