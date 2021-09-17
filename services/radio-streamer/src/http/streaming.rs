use crate::audio_formats::AudioFormats;
use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::channel::factory::ChannelPlayerFactory;
use crate::channel::registry::{ChannelKey, ChannelPlayerRegistry};
use crate::config::Config;
use crate::icy_metadata::{IcyMetadataMuxer, ICY_METADATA_INTERVAL};
use crate::transcoder::TranscoderService;
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::{SinkExt, StreamExt, TryStreamExt};
use serde::Deserialize;
use slog::{error, Logger};
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
pub async fn listen_by_channel_id(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    query_params: Query<ListenQueryParams>,
    backend_client: Data<Arc<BackendClient>>,
    transcoder: Data<Arc<TranscoderService>>,
    logger: Data<Arc<Logger>>,
    channel_player_factory: Data<Arc<ChannelPlayerFactory>>,
    channel_player_registry: Data<Arc<ChannelPlayerRegistry>>,
) -> impl Responder {
    let client_id = query_params.client_id.clone();

    let channel_info = match backend_client
        .get_channel_info(&channel_id, client_id.clone())
        .await
    {
        Ok(channel_info) => channel_info,
        Err(MorBackendClientError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unable to get channel info"; "error" => ?error);
            return HttpResponse::ServiceUnavailable().finish();
        }
    };

    let format_param = query_params.format.clone();

    let format = format_param
        .and_then(|f| AudioFormats::from_string(&f))
        .unwrap_or(AudioFormats::MP3_192K);

    let is_icy_enabled = request
        .headers()
        .get("icy-metadata")
        .filter(|v| v.to_str().unwrap() == "1")
        .is_some();

    let (enc_sender, enc_receiver) = match transcoder.encoder(&format) {
        Ok(ok) => ok,
        Err(error) => {
            error!(logger, "Unable to start audio encoder"; "error" => ?error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    let channel_player = {
        let channel_key = ChannelKey(*channel_id, client_id.clone());

        match channel_player_registry.get(&channel_key) {
            Some(channel_player) => channel_player,
            None => {
                let channel_player = channel_player_factory.create(*channel_id, client_id.clone());
                let channel_player = Arc::new(channel_player);

                channel_player_registry.register(channel_key, Arc::downgrade(&channel_player));

                channel_player
            }
        }
    };

    // Pipe audio data to encoder
    actix_rt::spawn({
        let channel_player = Arc::clone(&channel_player);
        let logger = logger.clone();

        let mut enc_sender = enc_sender;
        let mut audio_receiver = channel_player.audio_receiver.activate_cloned();

        async move {
            while let Some(bytes) = audio_receiver.next().await {
                if let Err(error) = enc_sender.send(Ok(bytes)).await {
                    error!(logger, "Unable to send audio data to encoder"; "error" => ?error);
                    break;
                }
            }

            drop(channel_player);
        }
    });

    {
        let mut response = HttpResponse::Ok();

        response.content_type(format.content_type).force_close();

        if is_icy_enabled {
            let (metadata_sender, metadata_receiver) = mpsc::unbounded();

            let mut icy_metadata_muxer = IcyMetadataMuxer::new(metadata_receiver);

            actix_rt::spawn({
                let channel_player = Arc::clone(&channel_player);
                let logger = logger.clone();

                let mut metadata_sender = metadata_sender;
                let mut title_receiver = channel_player.title_receiver.activate_cloned();

                async move {
                    while let Some(title) = title_receiver.next().await {
                        let metadata = format!("StreamTitle='{}';", &title);
                        if let Err(error) = metadata_sender.send(metadata.into_bytes()).await {
                            if is_icy_enabled {
                                error!(logger, "Unable to send metadata to client"; "error" => ?error);
                                break;
                            }
                        }
                    }

                    drop(channel_player);
                }
            });

            response
                .insert_header(("icy-metadata", "1"))
                .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)))
                .insert_header(("icy-name", format!("{}", &channel_info.name)));

            response.streaming({
                enc_receiver.map_ok(move |bytes| icy_metadata_muxer.handle_bytes(bytes))
            })
        } else {
            response.streaming(enc_receiver)
        }
    }
}
