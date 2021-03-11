use crate::audio_formats::AudioFormat;
use crate::codec::AudioCodecService;
use crate::icy_metadata::{IcyMetadataMuxer, ICY_METADATA_INTERVAL};
use crate::metrics::Metrics;
use crate::mor_backend_client::{MorBackendClient, MorBackendClientError};
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::{SinkExt, StreamExt};
use serde::Deserialize;
use slog::{debug, error, Logger};
use std::sync;
use std::sync::Arc;

#[derive(Deserialize, Clone)]
pub struct ListenQueryParams {
    f: Option<String>,
}

#[get("/listen/{channel_id}")]
pub async fn listen_by_channel_id(
    request: HttpRequest,
    channel_id: web::Path<u32>,
    query_params: Query<ListenQueryParams>,
    mor_backend_client: Data<Arc<MorBackendClient>>,
    audio_codec_service: Data<Arc<AudioCodecService>>,
    logger: Data<Arc<Logger>>,
    metrics: Data<Arc<Metrics>>,
) -> impl Responder {
    let channel_info = match mor_backend_client.get_channel_info(&channel_id).await {
        Ok(channel_info) => channel_info,
        Err(MorBackendClientError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unable to get channel info"; "error" => ?error);
            return HttpResponse::ServiceUnavailable().finish();
        }
    };

    let format_param = query_params.f.clone();
    let format = format_param
        .and_then(|f| AudioFormat::from_string(&f))
        .unwrap_or(AudioFormat::MP3_128k);

    let (enc_sender, enc_receiver) = match audio_codec_service.spawn_audio_encoder(&format) {
        Ok(ok) => ok,
        Err(error) => {
            error!(logger, "Unable to start audio encoder"; "error" => ?error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    let is_icy_enabled = request
        .headers()
        .get("icy-metadata")
        .filter(|v| v.to_str().unwrap() == "1")
        .is_some();

    let (metadata_sender, metadata_receiver) = sync::mpsc::sync_channel(1);

    actix_rt::spawn({
        let mut enc_sender = enc_sender;
        let logger = logger.clone();

        async move {
            metrics.inc_streaming_in_progress();

            'outer: loop {
                let now_playing = match mor_backend_client.get_now_playing(&channel_id).await {
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
                if let Err(error) = metadata_sender.send(metadata.into_bytes()) {
                    if is_icy_enabled {
                        error!(logger, "Unable to send track title"; "error" => ?error);
                        break;
                    }
                }

                while let Some(r) = dec_receiver.next().await {
                    if let Err(error) = enc_sender.send(r).await {
                        error!(logger, "Unable to pipe bytes"; "error" => ?error);
                        break 'outer;
                    }
                }
            }

            metrics.dec_streaming_in_progress();
        }
    });

    let mut response = HttpResponse::Ok();

    response.content_type(format.content_type()).force_close();

    if is_icy_enabled {
        response
            .insert_header(("icy-metadata", "1"))
            .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)))
            .insert_header(("icy-name", format!("{}", &channel_info.name)));

        let icy_metadata_muxer = IcyMetadataMuxer::new(metadata_receiver);

        response.streaming({
            let mut icy_metadata_muxer = icy_metadata_muxer;
            enc_receiver.map(move |r| r.map(|bytes| icy_metadata_muxer.handle_bytes(bytes)))
        })
    } else {
        response.streaming(enc_receiver)
    }
}
