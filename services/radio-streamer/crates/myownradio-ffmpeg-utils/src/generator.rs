use crate::{
    Frame, Timestamp, INTERNAL_CHANNELS_NUMBER, INTERNAL_SAMPLING_FREQUENCY, INTERNAL_TIME_BASE,
};
use futures::channel::mpsc::{channel, Receiver};
use futures::SinkExt;
use std::time::Duration;

const SILENCE_FRAME_SIZE: usize = 1024;

pub fn generate_silence(duration: Option<&Duration>) -> Receiver<Frame> {
    let (mut sender, receiver) = channel(0);

    let num_frames = match duration {
        Some(duration) => {
            let dur_millis = duration.as_millis() as i64;
            (dur_millis * INTERNAL_SAMPLING_FREQUENCY * INTERNAL_CHANNELS_NUMBER) / 1000
        }
        None => i64::MAX,
    };

    let duration = Timestamp::new(SILENCE_FRAME_SIZE as i64, INTERNAL_TIME_BASE);

    actix_rt::spawn({
        async move {
            for frame in 0..num_frames {
                let pts =
                    Timestamp::new(frame as i64 * SILENCE_FRAME_SIZE as i64, INTERNAL_TIME_BASE);
                let duration = duration.clone();

                let data = vec![0u8; SILENCE_FRAME_SIZE];
                let buffer = Frame::new(pts, duration, data);

                if let Err(_) = sender.send(buffer).await {
                    break;
                }
            }
        }
    });

    receiver
}
