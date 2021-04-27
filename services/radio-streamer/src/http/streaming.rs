use std::sync::Arc;
use std::time::Duration;

use actix_web::web::{Data, Query};
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
        RAW_AUDIO_STEREO_BYTE_RATE,
        RAW_AUDIO_STEREO_BYTE_RATE * PREFETCH_TIME.as_secs() as usize,
    );

    spawn_pipe_channel(thr_receiver, enc_sender);

    let (metadata_sender, metadata_receiver) = mpsc::unbounded();

    actix_rt::spawn({
        let mut thr_sender = thr_sender;
        let mut metadata_sender = metadata_sender;

        let client_id = client_id.clone();
        let logger = logger.clone();

        let mut track_a: Arc<Mutex<_>> = Arc::new(Mutex::new(None));
        let mut track_b: Arc<Mutex<_>> = Arc::new(Mutex::new(None));

        let mut pre_spawned_receiver: Arc<Mutex<_>> = Arc::new(Mutex::new(None));

        async move {
            metrics.inc_streaming_in_progress();

            loop {
                let now_playing = match mor_backend_client
                    .get_now_playing(&channel_id, client_id.clone(), &PREFETCH_TIME)
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

                let (restart_signal_tx, mut restart_signal_rx) = oneshot::channel();

                let current_track = now_playing.current_track;
                let next_track = now_playing.next_track;

                let current_track_left = current_track.duration - current_track.offset;
                let should_finish_at = Instant::now() + current_track_left;

                let current_track_receiver = match pre_spawned_receiver.lock().await.take() {
                    Some(receiver)
                        if current_track.offset < ALLOWED_DELAY_FOR_PRE_SPAWNED_RECEIVER =>
                    {
                        receiver
                    }
                    _ => {
                        match audio_codec_service
                            .spawn_audio_decoder(&current_track.url, &current_track.offset)
                        {
                            Ok(receiver) => receiver,
                            Err(error) => {
                                error!(logger, "Unable to spawn current track decoder"; "error" => ?error);
                                break;
                            }
                        }
                    }
                };

                match audio_codec_service.spawn_audio_decoder(&next_track.url, &Duration::default())
                {
                    Ok(receiver) => {
                        pre_spawned_receiver.lock().await.replace(receiver);
                    }
                    Err(error) => {
                        error!(logger, "Unable to spawn next track decoder"; "error" => ?error);
                        break;
                    }
                }

                if is_icy_enabled {
                    let metadata = format!("StreamTitle='{}';", &current_track.title);
                    if let Err(error) = metadata_sender.send(metadata.into_bytes()).await {
                        if is_icy_enabled {
                            error!(logger, "Unable to send track title"; "error" => ?error);
                            break;
                        }
                    }
                }

                let uuid = restart_registry
                    .register_restart_sender(&channel_id, restart_signal_tx)
                    .await;

                let mut track_receiver = track_a
                    .lock()
                    .await
                    .take()
                    .expect("Unable to take track receiver");

                let result = pipe_channel_with_cancel(
                    &mut track_receiver,
                    &mut thr_sender,
                    &mut restart_signal_rx,
                )
                .await;

                if result.is_ok() {
                    debug!(logger, "Sleep if necessary...");

                    if let Err(error) =
                        sleep_until_deadline(should_finish_at, &mut restart_signal_rx).await
                    {
                        warn!(logger, "Sleep cancelled");
                    }
                }

                restart_registry
                    .unregister_restart_sender(&channel_id, uuid)
                    .await;

                match result {
                    Ok(_) => (),
                    Err(PipeChannelError::CancelError(_)) => {
                        debug!(logger, "Received restart signal...");
                        pre_spawned_receiver.lock().await.clear();
                    }
                    Err(PipeChannelError::SendError(error)) => {
                        error!(logger, "Unable to pipe bytes"; "error" => ?error);
                        break;
                    }
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
