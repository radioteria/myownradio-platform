use crate::backend_client::BackendClient;
use crate::config::Config;
use crate::metrics::Metrics;
use crate::stream::channel_player::{ChannelPlayer, ChannelPlayerError, ChannelPlayerMessage};
use crate::stream::player_loop::PlayerLoopError;
use crate::stream::types::TimedBuffer;
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::{io, SinkExt, StreamExt};
use serde::Deserialize;
use slog::{debug, error, Logger};
use std::sync::Arc;

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
    let shared_player = match ChannelPlayer::create(
        &channel_id,
        &query_params.client_id,
        &config.path_to_ffmpeg,
        &backend_client,
        &logger,
        &metrics,
    )
    .await
    {
        Ok(shared_player) => shared_player,
        Err(ChannelPlayerError::PlayerLoopError(PlayerLoopError::ChannelNotFound)) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unexpected error on starting shared player"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };
    let mut player_messages = shared_player.create_receiver();

    let (bytes_tx, bytes_rx) = mpsc::channel::<Result<_, io::Error>>(0);

    actix_rt::spawn({
        let mut bytes_tx = bytes_tx.clone();

        async move {
            while let Some(event) = player_messages.next().await {
                match event {
                    ChannelPlayerMessage::TimedBuffer(TimedBuffer(bytes, _)) => {
                        if let Err(_) = bytes_tx.send(Ok(bytes)).await {
                            break;
                        }
                    }
                    ChannelPlayerMessage::ChannelTitle(title) => {
                        debug!(logger, "Received channel title"; "name" => title);
                    }
                    ChannelPlayerMessage::TrackTitle(title) => {
                        debug!(logger, "Received track title"; "title" => title);
                    }
                }
            }

            drop(shared_player);
        }
    });

    let mut response = HttpResponse::Ok();

    response
        .content_type("audio/l16;rate=48000;channels=2;endianness=little-endian")
        .force_close();

    response.streaming(bytes_rx)
}
