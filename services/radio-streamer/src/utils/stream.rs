use actix_rt::time::Instant;
use actix_web::web::Bytes;
use futures::channel::mpsc;
use futures::{SinkExt, StreamExt};
use std::cmp::max;
use std::time::Duration;

const THROTTLE_DURATION_MS: Duration = Duration::from_millis(50);

pub fn throttled_audio_data(
    bytes_per_second: usize,
    prefetch_size: usize,
    sync_to_silence: bool,
) -> (mpsc::Sender<Bytes>, mpsc::Receiver<Bytes>) {
    let bytes_per_second = bytes_per_second as isize;
    let prefetch_size = prefetch_size as isize;

    let (in_sender, in_receiver) = mpsc::channel::<Bytes>(0);
    let (out_sender, out_receiver) = mpsc::channel(0);

    actix_rt::spawn({
        let mut in_receiver = in_receiver;
        let mut out_sender = out_sender;

        let start = Instant::now();

        async move {
            let mut bytes_transferred = -prefetch_size;

            while let Some(bytes) = in_receiver.next().await {
                bytes_transferred += bytes.len() as isize;

                let start = start.elapsed().as_secs() as isize;
                let actual_bytes_per_second = bytes_transferred / max(start, 1isize);

                if actual_bytes_per_second > bytes_per_second {
                    actix_rt::time::sleep(THROTTLE_DURATION_MS).await;
                }

                if let Err(_) = out_sender.send(bytes).await {
                    break;
                }
            }
        }
    });

    (in_sender, out_receiver)
}
