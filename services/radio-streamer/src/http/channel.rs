use crate::backend_client::BackendClient;
use crate::config::Config;
use crate::metrics::Metrics;
use crate::stream::player_loop::{make_player_loop, PlayerLoopError, PlayerLoopEvent};
use crate::stream::types::TimedBuffer;
use actix_web::web::{Bytes, Data, Query};
use actix_web::{get, web, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::{io, SinkExt, StreamExt};
use serde::Deserialize;
use slog::{debug, error, Logger};
use std::sync::{Arc, Mutex};

#[derive(Deserialize, Clone)]
pub struct ListenQueryParams {
    format: Option<String>,
    client_id: Option<String>,
}

#[get("/channel-test/{channel_id}")]
pub(crate) async fn test_channel_playback(
    channel_id: web::Path<usize>,
    query_params: Query<ListenQueryParams>,
    backend_client: Data<Arc<BackendClient>>,
    logger: Data<Arc<Logger>>,
    metrics: Data<Arc<Metrics>>,
    config: Data<Arc<Config>>,
) -> impl Responder {
    let mut player_loop_events = match make_player_loop(
        &channel_id,
        &query_params.client_id,
        &config.path_to_ffmpeg,
        &backend_client,
        &logger,
        &metrics,
    )
    .await
    {
        Ok(player_loop_events) => player_loop_events,
        Err(PlayerLoopError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unexpected error on starting player loop"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let (bytes_tx, bytes_rx) = mpsc::channel::<Result<_, io::Error>>(0);
    let restart_sender = Mutex::new(Option::None);

    actix_rt::spawn({
        let mut bytes_tx = bytes_tx.clone();

        async move {
            while let Some(event) = player_loop_events.next().await {
                match event {
                    PlayerLoopEvent::TimedBuffer(TimedBuffer(bytes, _)) => {
                        if let Err(error) = bytes_tx.send(Ok(bytes)).await {
                            break;
                        }
                    }
                    PlayerLoopEvent::ChannelName(name) => {
                        debug!(logger, "Received channel name"; "name" => name);
                    }
                    PlayerLoopEvent::NewTitle(title) => {
                        debug!(logger, "Received new title"; "title" => title);
                    }
                    PlayerLoopEvent::RestartSender(sender) => {
                        debug!(logger, "Received restart sender");
                        let _ = restart_sender.lock().unwrap().replace(sender);
                    }
                }
            }

            drop(restart_sender);
        }
    });

    let mut response = HttpResponse::Ok();

    response
        .content_type("audio/l16;rate=48000;channels=2;endianness=little-endian")
        .force_close();

    response.streaming(bytes_rx)
}
