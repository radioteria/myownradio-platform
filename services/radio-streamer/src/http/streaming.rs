use std::sync::Arc;
use std::time::Duration;

use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::{mpsc, oneshot};
use futures::{SinkExt, TryStreamExt};
use serde::Deserialize;
use slog::{debug, error, Logger};

use crate::audio_formats::AudioFormats;
use crate::codec::AudioCodecService;
use crate::config::Config;
use crate::helpers::io::{pipe_channel_with_cancel, spawn_pipe_channel, throttled_channel};
use crate::icy_metadata::{IcyMetadataMuxer, ICY_METADATA_INTERVAL};
use crate::metrics::Metrics;
use crate::mor_backend_client::{MorBackendClient, MorBackendClientError};
use crate::restart_registry::RestartRegistry;

const PREFETCH_AUDIO: Duration = Duration::from_secs(3);
const DECODED_AUDIO_BYTES_PER_SECOND: usize = 176400;

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

    let (thr_sender, thr_receiver) = throttled_channel(
        DECODED_AUDIO_BYTES_PER_SECOND,
        DECODED_AUDIO_BYTES_PER_SECOND * PREFETCH_AUDIO.as_secs() as usize,
    );

    spawn_pipe_channel(thr_receiver, enc_sender);

    let (metadata_sender, metadata_receiver) = mpsc::channel(1);

    actix_rt::spawn({
        let mut thr_sender = thr_sender;
        let mut metadata_sender = metadata_sender;

        let client_id = client_id.clone();
        let logger = logger.clone();

        async move {
            metrics.inc_streaming_in_progress();

            let mut is_first_track = true;

            loop {
                let (restart_signal_tx, mut restart_signal_rx) = oneshot::channel();

                let now_playing = match mor_backend_client
                    .get_now_playing(&channel_id, client_id.clone())
                    .await
                {
                    Ok(now_playing) => {
                        debug!(logger, "Now playing: {:?}", &now_playing);
                        now_playing
                    }
                    Err(MorBackendClientError::ChannelNotFound) => {
                        // Channel was deleted when streaming. Nothing special.
                        break;
                    }
                    Err(error) => {
                        error!(logger, "Unable to get now playing"; "error" => ?error);
                        break;
                    }
                };

                if is_first_track {
                    is_first_track = false;
                } else {
                    metrics.set_track_start_lateness(&now_playing.current_track.offset);
                }

                let mut dec_receiver = match audio_codec_service.spawn_audio_decoder(
                    &now_playing.current_track.url,
                    &now_playing.current_track.offset,
                ) {
                    Ok(receiver) => receiver,
                    Err(error) => {
                        error!(logger, "Unable to decode audio file"; "error" => ?error);
                        break;
                    }
                };

                let metadata = format!("StreamTitle='{}';", &now_playing.current_track.title);
                if let Err(error) = metadata_sender.send(metadata.into_bytes()).await {
                    if is_icy_enabled {
                        error!(logger, "Unable to send track title"; "error" => ?error);
                        break;
                    }
                }

                let uuid = restart_registry
                    .register_restart_sender(&channel_id, restart_signal_tx)
                    .await;

                let result = pipe_channel_with_cancel(
                    &mut dec_receiver,
                    &mut thr_sender,
                    &mut restart_signal_rx,
                )
                .await;

                restart_registry
                    .unregister_restart_sender(&channel_id, uuid)
                    .await;

                if let Err(error) = result {
                    error!(logger, "Unable to pipe bytes"; "error" => ?error);

                    break;
                }
            }

            metrics.dec_streaming_in_progress();
        }
    });

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
