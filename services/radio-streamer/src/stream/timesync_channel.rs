use futures::channel::mpsc;
use futures::SinkExt;
use futures_lite::StreamExt;
use std::time::{Duration, SystemTime};

pub(crate) trait TimeSyncPacket {
    fn dts(&self) -> &Duration;
}

pub(crate) fn timesync_channel<M: TimeSyncPacket + 'static>(
    offset: SystemTime,
) -> (mpsc::Sender<M>, mpsc::Receiver<M>) {
    let (input_sender, mut input_receiver) = mpsc::channel::<M>(0);
    let (mut output_sender, output_receiver) = mpsc::channel::<M>(0);

    actix_rt::spawn({
        let mut dts_offset = Duration::default();

        async move {
            let mut previous_dts = Duration::default();

            while let Some(msg) = input_receiver.next().await {
                let dts = *msg.dts();
                dts_offset += dts - previous_dts;
                previous_dts = dts;

                let sleep_dur = (offset + dts_offset).duration_since(SystemTime::now()).ok();

                if let Some(duration) = sleep_dur {
                    actix_rt::time::sleep(duration).await;
                }

                if let Err(_) = output_sender.send(msg).await {
                    break;
                }
            }
        }
    });

    (input_sender, output_receiver)
}
