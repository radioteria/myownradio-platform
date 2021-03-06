use actix_web::web::Bytes;
use futures::channel::mpsc::{Receiver, Sender};
use futures::channel::oneshot;
use futures::channel::oneshot::Canceled;
use futures::{io, SinkExt, StreamExt};
use slog::{error, Logger};

pub async fn pipe(
    rx: &'static mut Receiver<Result<Bytes, io::Error>>,
    tx: &'static mut Sender<Result<Bytes, io::Error>>,
    logger: &Logger,
) -> Result<(), Canceled> {
    let (finish_signal, finish_handler) = oneshot::channel::<()>();

    actix_rt::spawn({
        let logger = logger.clone();

        async move {
            while let Some(r) = rx.next().await {
                if let Err(error) = tx.send(r).await {
                    error!(logger, "Unable to pipe bytes"; "error" => ?error);
                    break;
                }
            }
            finish_signal.send(()).unwrap();
        }
    });

    finish_handler.await
}
