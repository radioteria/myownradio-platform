use super::utils::icy_muxer::{IcyMuxer, ICY_METADATA_INTERVAL};
use crate::audio_formats::{AudioFormat, AudioFormats};
use crate::audio_stream::AudioStreamMessage;
use crate::config::Config;
use crate::stream_compositor::StreamCompositor;
use crate::types::ChannelId;
use actix_web::web::{Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::{SinkExt, StreamExt};
use myownradio_ffmpeg_utils::OutputFormat;
use serde::Deserialize;
use std::sync::Arc;

#[get("/active")]
pub(crate) async fn get_active_channel_ids() -> impl Responder {
    HttpResponse::Ok().json(serde_json::json!({ "channel_ids": None::<usize> }))
}

#[get("/v2/restart/{channel_id}")]
pub(crate) async fn restart_channel_by_id_v2(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    config: Data<Arc<Config>>,
    stream_compositor: Data<StreamCompositor>,
) -> impl Responder {
    let channel_id = channel_id.into_inner();

    let actual_token = match request.headers().get("token").and_then(|v| v.to_str().ok()) {
        Some(token) => token,
        None => {
            return HttpResponse::Unauthorized().finish();
        }
    };

    if actual_token != config.stream_mutation_token {
        return HttpResponse::Unauthorized().finish();
    }

    stream_compositor
        .restart_channel_streams(&channel_id.into())
        .await;

    HttpResponse::Ok().finish()
}

const SAMPLING_RATE: u32 = 44_100;

impl Into<OutputFormat> for AudioFormat {
    fn into(self) -> OutputFormat {
        match self.codec {
            "libmp3lame" => OutputFormat::MP3 {
                bit_rate: (self.bitrate as usize) * 1000,
                sampling_rate: SAMPLING_RATE,
            },
            "libfdk_aac" => OutputFormat::AAC {
                bit_rate: (self.bitrate as usize) * 1000,
                sampling_rate: SAMPLING_RATE,
            },
            _ => todo!(),
        }
    }
}

#[derive(Deserialize, Clone)]
pub struct GetChannelAudioStreamV3QueryParams {
    format: Option<String>,
}

#[get("/v3/listen/{channel_id}")]
pub(crate) async fn get_channel_audio_stream_v3(
    request: HttpRequest,
    channel_id: web::Path<u64>,
    query_params: Query<GetChannelAudioStreamV3QueryParams>,
    stream_compositor: Data<StreamCompositor>,
) -> impl Responder {
    let channel_id: ChannelId = channel_id.into_inner().into();

    let format = query_params
        .into_inner()
        .format
        .and_then(|format| AudioFormats::from_string(&format))
        .unwrap_or_default();
    let is_icy_enabled = request
        .headers()
        .get("icy-metadata")
        .filter(|v| v.to_str().unwrap() == "1")
        .is_some();
    let content_type = format.content_type;
    let output_format: OutputFormat = format.into();

    let stream = match stream_compositor
        .get_or_create_audio_stream(&channel_id, &output_format)
        .await
    {
        Ok(audio_stream) => audio_stream,
        Err(error) => {
            tracing::error!(?error, "Unable to get audio stream");
            return HttpResponse::InternalServerError().finish();
        }
    };

    let channel_name = stream.channel_info().name.clone();

    let audio_stream_messages = match stream.subscribe() {
        Ok(audio_stream_messages) => audio_stream_messages,
        Err(error) => {
            tracing::error!(?error, "Unable to subscribe to audio stream messages");
            return HttpResponse::InternalServerError().finish();
        }
    };

    let icy_muxer = Arc::new(IcyMuxer::new());

    if let Some(title) = stream.current_title().await {
        icy_muxer.send_track_title(title);
    }

    let (response_sender, response_receiver) = mpsc::channel(32);

    actix_rt::spawn({
        let mut audio_stream_messages = audio_stream_messages;
        let mut response_sender = response_sender;

        let icy_muxer = icy_muxer.clone();

        async move {
            while let Some(msg) = audio_stream_messages.next().await {
                match msg {
                    AudioStreamMessage::Buffer { bytes, .. } => {
                        if response_sender.send(bytes).await.is_err() {
                            break;
                        }
                    }
                    AudioStreamMessage::TrackTitle { title, .. } => {
                        icy_muxer.send_track_title(title);
                    }
                }
            }

            drop(stream);
        }
    });

    let mut response = HttpResponse::Ok();

    response.content_type(content_type).force_close();

    if is_icy_enabled {
        response
            .insert_header(("icy-metadata", "1"))
            .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)))
            .insert_header(("icy-name", format!("{}", channel_name)));

        response.streaming::<_, actix_web::Error>(
            response_receiver
                .map(move |bytes| icy_muxer.handle_bytes(bytes))
                .map(Ok),
        )
    } else {
        response.streaming::<_, actix_web::Error>(response_receiver.map(Ok))
    }
}
