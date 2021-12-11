use crate::audio_formats::AudioFormats;
use crate::backend_client::{BackendClient, MorBackendClientError};
use crate::config::Config;
use crate::stream::channel_encoder::ChannelEncoderMessage;
use crate::stream::icy_muxer::{IcyMuxer, ICY_METADATA_INTERVAL};
use crate::{EncoderRegistry, PlayerRegistry};
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::StreamExt;
use serde::Deserialize;
use slog::{debug, error, warn, Logger};
use std::sync::Arc;

#[get("/active")]
pub(crate) async fn get_active_channel_ids(
    player_registry: Data<PlayerRegistry>,
) -> impl Responder {
    let channel_ids = player_registry.get_channel_ids();

    HttpResponse::Ok().json(serde_json::json!({ "channel_ids": channel_ids }))
}

#[get("/restart/{channel_id}")]
pub(crate) async fn restart_channel_by_id(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    config: Data<Arc<Config>>,
    player_registry: Data<PlayerRegistry>,
) -> impl Responder {
    let actual_token = match request.headers().get("token").and_then(|v| v.to_str().ok()) {
        Some(token) => token,
        None => {
            return HttpResponse::Unauthorized().finish();
        }
    };

    if actual_token != config.stream_mutation_token {
        return HttpResponse::Unauthorized().finish();
    }

    player_registry.restart_by_channel_id(&channel_id);

    HttpResponse::Ok().finish()
}

#[derive(Deserialize, Clone)]
pub struct GetChannelAudioStreamQueryParams {
    format: Option<String>,
    client_id: Option<String>,
}

#[get("/listen/{channel_id}")]
pub(crate) async fn get_channel_audio_stream(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    query_params: Query<GetChannelAudioStreamQueryParams>,
    logger: Data<Arc<Logger>>,
    encoder_registry: Data<EncoderRegistry>,
    backend_client: Data<Arc<BackendClient>>,
) -> impl Responder {
    let channel_info = match backend_client
        .get_channel_info(&channel_id.clone(), query_params.client_id.clone())
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
        .and_then(|format| AudioFormats::from_string(&format))
        .unwrap_or_default();

    let is_icy_enabled = request
        .headers()
        .get("icy-metadata")
        .filter(|v| v.to_str().unwrap() == "1")
        .is_some();

    let encoder = match encoder_registry
        .get_encoder(&channel_id, &query_params.client_id, &format)
        .await
    {
        Ok(encoder) => encoder,
        Err(error) => {
            error!(logger, "Error"; "error" => ?error);

            return HttpResponse::InternalServerError().finish();
        }
    };

    let encoder_messages = encoder.create_receiver();

    let icy_muxer = Arc::new(IcyMuxer::new());
    let (response_sender, response_receiver) = mpsc::channel(512);

    icy_muxer.send_track_title(encoder.get_track_title().unwrap_or_default());

    actix_rt::spawn({
        let mut encoder_messages = encoder_messages;
        let mut response_sender = response_sender;

        let icy_muxer = Arc::downgrade(&icy_muxer);

        async move {
            while let Some(event) = encoder_messages.next().await {
                match event {
                    ChannelEncoderMessage::EncodedBuffer(bytes) => {
                        // Do not block encoder if one of the consumers are blocked due to network issues
                        match response_sender.try_send(bytes) {
                            Err(error) if error.is_disconnected() => {
                                // Disconnected: drop the receiver
                                break;
                            }
                            Err(error) if error.is_full() => {
                                // Buffer is full: skip the remaining bytes
                            }
                            Err(error) => {
                                warn!(logger, "Unable to send bytes to the client"; "error" => ?error);
                                // Unexpected error: drop the receiver
                                break;
                            }
                            Ok(_) => (),
                        }
                    }
                    ChannelEncoderMessage::TrackTitle(title) => {
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
                .insert_header(("icy-name", channel_info.name));

            response.streaming::<_, actix_web::Error>({
                response_receiver
                    .map(move |bytes| icy_muxer.handle_bytes(bytes))
                    .map::<Result<_, actix_web::Error>, _>(Ok)
            })
        } else {
            response.streaming::<_, actix_web::Error>(response_receiver.map(Ok))
        }
    }
}
