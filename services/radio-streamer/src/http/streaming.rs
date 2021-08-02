use std::sync::Arc;
use std::time::Duration;

use actix_web::web::{Bytes, Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, TryStreamExt};
use serde::Deserialize;
use slog::{debug, error, warn, Logger};

use crate::audio_formats::AudioFormats;
use crate::codec::AudioCodecService;
use crate::config::Config;
use crate::constants::{
    ALLOWED_DELAY_FOR_PRE_SPAWNED_RECEIVER, PREFETCH_TIME, RAW_AUDIO_STEREO_BYTE_RATE,
};
use crate::helpers::io::{
    pipe_channel_with_cancel, sleep_until_deadline, spawn_pipe_channel, throttled_channel,
    PipeChannelError,
};
use crate::icy_metadata::{IcyMetadataMuxer, ICY_METADATA_INTERVAL};
use crate::metrics::Metrics;
use crate::mor_backend_client::{MorBackendClient, MorBackendClientError};
use crate::restart_registry::RestartRegistry;
use crate::stream::channel_player_factory::ChannelPlayerFactory;
use actix_rt::time::Instant;
use futures::lock::Mutex;
use futures_lite::StreamExt;

#[get("/streams")]
pub async fn get_active_streams(restart_registry: Data<Arc<RestartRegistry>>) -> impl Responder {
    let channels = restart_registry.get_channels().await;

    HttpResponse::Ok().json(channels)
}

#[get("/restart/{channel_id}")]
pub async fn restart_by_channel_id(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    restart_registry: Data<Arc<RestartRegistry>>,
    config: Data<Arc<Config>>,
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

    restart_registry.restart(&channel_id).await;

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
    mor_backend_client: Data<Arc<MorBackendClient>>,
    audio_codec_service: Data<Arc<AudioCodecService>>,
    logger: Data<Arc<Logger>>,
    metrics: Data<Arc<Metrics>>,
    restart_registry: Data<Arc<RestartRegistry>>,
    channel_player_factory: Data<Arc<ChannelPlayerFactory>>,
) -> impl Responder {
    let client_id = query_params.client_id.clone();

    let channel_info = match mor_backend_client
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

    let (enc_sender, enc_receiver) = match audio_codec_service.spawn_audio_encoder(&format) {
        Ok(ok) => ok,
        Err(error) => {
            error!(logger, "Unable to start audio encoder"; "error" => ?error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    let (metadata_sender, metadata_receiver) = mpsc::unbounded();

    let channel_player =
        channel_player_factory.create_channel_player(*channel_id, client_id.clone());

    // Pipe audio data to encoder
    actix_rt::spawn({
        let mut enc_sender = enc_sender;
        let mut audio_receiver = channel_player.audio_receiver;

        let logger = logger.clone();

        async move {
            while let Some(bytes) = audio_receiver.next().await {
                if let Err(error) = enc_sender.send(Ok(bytes)).await {
                    error!(logger, "Unable to send audio data to encoder"; "error" => ?error);
                    break;
                }
            }
        }
    });

    if is_icy_enabled {
        actix_rt::spawn({
            let mut metadata_sender = metadata_sender;
            let mut title_receiver = channel_player.title_receiver;

            let logger = logger.clone();

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
            }
        });
    }

    let mut response = HttpResponse::Ok();

    response.content_type(format.content_type).force_close();

    if is_icy_enabled {
        response
            .insert_header(("icy-metadata", "1"))
            .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)))
            .insert_header(("icy-name", format!("{}", &channel_info.name)));

        let icy_metadata_muxer = IcyMetadataMuxer::new(metadata_receiver);

        response.streaming({
            let mut icy_metadata_muxer = icy_metadata_muxer;
            enc_receiver.map_ok(move |bytes| icy_metadata_muxer.handle_bytes(bytes))
        })
    } else {
        response.streaming(enc_receiver)
    }
}
