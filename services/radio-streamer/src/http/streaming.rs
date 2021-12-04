use crate::audio_formats::AudioFormats;
use crate::channel::registry::ChannelPlayerRegistry;
use crate::config::Config;
use crate::stream::channel_player::ChannelPlayerMessage;
use crate::stream::ffmpeg_encoder::make_ffmpeg_encoder;
use crate::stream::icy_muxer::{IcyMuxer, ICY_METADATA_INTERVAL};
use crate::stream::player_registry::PlayerRegistryError;
use crate::stream::types::TimedBuffer;
use crate::{Metrics, PlayerRegistry};
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::{SinkExt, StreamExt};
use serde::Deserialize;
use slog::{debug, error, Logger};
use std::sync::Arc;

#[get("/streams")]
pub async fn get_active_streams(
    channel_player_registry: Data<Arc<ChannelPlayerRegistry>>,
) -> impl Responder {
    let channels = channel_player_registry.get_channel_ids();

    HttpResponse::Ok().json(channels)
}

#[get("/restart/{channel_id}")]
pub async fn restart_by_channel_id(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    config: Data<Arc<Config>>,
    channel_player_registry: Data<Arc<ChannelPlayerRegistry>>,
) -> impl Responder {
    let actual_token = match request.headers().get("token").map(|v| v.to_str()) {
        Some(Ok(token)) => token,
        _ => {
            return HttpResponse::Unauthorized().finish();
        }
    };

    if actual_token != config.stream_mutation_token {
        return HttpResponse::Unauthorized().finish();
    }

    for channel_player in channel_player_registry.get_by_id(&channel_id) {
        channel_player.restart().await;
    }

    HttpResponse::Ok().finish()
}

#[derive(Deserialize, Clone)]
pub struct ListenQueryParams {
    format: Option<String>,
    client_id: Option<String>,
}

#[get("/listen/{channel_id}")]
pub(crate) async fn listen_channel(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    query_params: Query<ListenQueryParams>,
    logger: Data<Arc<Logger>>,
    metrics: Data<Arc<Metrics>>,
    player_registry: Data<PlayerRegistry>,
    config: Data<Arc<Config>>, // TODO Remove from app data
) -> impl Responder {
    eprintln!("Here");

    let format_param = query_params.format.clone();

    let format = format_param
        .and_then(|f| AudioFormats::from_string(&f))
        .unwrap_or(AudioFormats::MP3_320K);

    let is_icy_enabled = request
        .headers()
        .get("icy-metadata")
        .filter(|v| v.to_str().unwrap() == "1")
        .is_some();

    let channel_player = match player_registry
        .get_player(&channel_id, &query_params.client_id)
        .await
    {
        Ok(channel_player) => channel_player,
        Err(PlayerRegistryError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Error"; "error" => ?error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    let player_messages = channel_player.create_receiver();

    let (encoder_sender, encoder_receiver) =
        match make_ffmpeg_encoder(&format, &config.path_to_ffmpeg, &logger, &metrics) {
            Ok(res) => res,
            Err(error) => {
                error!(logger, "Error"; "error" => ?error);
                return HttpResponse::InternalServerError().finish();
            }
        };

    let icy_muxer = Arc::new(IcyMuxer::new());

    actix_rt::spawn({
        let mut encoder_sender = encoder_sender;
        let mut player_messages = player_messages;

        let icy_muxer = Arc::downgrade(&icy_muxer);

        async move {
            while let Some(event) = player_messages.next().await {
                match event {
                    ChannelPlayerMessage::TimedBuffer(TimedBuffer(bytes, _)) => {
                        if let Err(_) = encoder_sender.send(bytes).await {
                            break;
                        }
                    }
                    ChannelPlayerMessage::ChannelTitle(title) => {
                        debug!(logger, "Received channel title"; "name" => ?title);
                    }
                    ChannelPlayerMessage::TrackTitle(title) => {
                        debug!(logger, "Received track title"; "title" => ?title);

                        if let Some(muxer) = icy_muxer.upgrade() {
                            muxer.send_track_title(title);
                        }
                    }
                }
            }
        }
    });

    {
        let mut response = HttpResponse::Ok();

        response.content_type(format.content_type).force_close();

        if is_icy_enabled {
            response
                .insert_header(("icy-metadata", "1"))
                .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)))
                .insert_header((
                    "icy-name",
                    channel_player.get_channel_title().unwrap_or_default(),
                ));

            response.streaming::<_, actix_web::Error>({
                encoder_receiver
                    .map(move |bytes| icy_muxer.handle_bytes(bytes))
                    .map::<Result<_, actix_web::Error>, _>(Ok)
            })
        } else {
            response.streaming::<_, actix_web::Error>(encoder_receiver.map(Ok))
        }
    }
}
