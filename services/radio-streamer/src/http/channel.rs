use super::utils::icy_muxer::{IcyMuxer, ICY_METADATA_INTERVAL};
use crate::audio_formats::{AudioFormat, AudioFormats};
use crate::audio_stream::AudioStreamMessage;
use crate::backend_client::{BackendClient, GetChannelInfoError, GetNowPlayingError, NowPlaying};
use crate::config::Config;
use crate::stream::{StreamCreateError, StreamMessage, StreamsRegistry, StreamsRegistryExt};
use crate::stream_compositor::StreamCompositor;
use crate::types::ChannelId;
use actix_web::web::{Bytes, Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::executor::{block_on, LocalSpawner};
use futures::{SinkExt, StreamExt};
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::{NowPlayingClient, NowPlayingError, PlayerLoop};
use serde::Deserialize;
use slog::{debug, error, warn, Drain, Logger};
use std::sync::{Arc, Mutex};
use std::time::{Duration, SystemTime};

#[get("/active")]
pub(crate) async fn get_active_channel_ids(
    stream_registry: Data<Arc<StreamsRegistry>>,
) -> impl Responder {
    let channel_ids = stream_registry.get_channel_ids();

    HttpResponse::Ok().json(serde_json::json!({ "channel_ids": channel_ids }))
}

#[get("/v2/restart/{channel_id}")]
pub(crate) async fn restart_channel_by_id_v2(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    config: Data<Arc<Config>>,
    stream_registry: Data<Arc<StreamsRegistry>>,
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

    stream_registry.restart_stream(&channel_id);

    stream_compositor
        .restart_channel_streams(&channel_id.into())
        .await;

    HttpResponse::Ok().finish()
}

#[derive(Deserialize, Clone)]
pub struct GetChannelAudioStreamQueryParams {
    format: Option<String>,
}

#[get("/v2/listen/{channel_id}")]
pub(crate) async fn get_channel_audio_stream_v2(
    request: HttpRequest,
    channel_id: web::Path<usize>,
    query_params: Query<GetChannelAudioStreamQueryParams>,
    logger: Data<Arc<Logger>>,
    stream_registry: Data<Arc<StreamsRegistry>>,
) -> impl Responder {
    // Handle "Range" header in the request for real-time audio streaming and return a 416 status code (Range Not Satisfiable) if it's present.
    // This handler is designed for real-time audio streaming and does not support ranges.
    if let Some(header) = request.headers().get("range") {
        if header.to_str().unwrap() != "bytes=0-" {
            return HttpResponse::RangeNotSatisfiable()
                .insert_header(("accept-ranges", "none"))
                .body("The requested range is not satisfiable");
        }
    }

    let channel_id = channel_id.into_inner();
    let stream = match stream_registry.get_or_create_stream(&channel_id).await {
        Ok(stream) => stream,
        Err(StreamCreateError::ChannelNotFound) => {
            return HttpResponse::NotFound().finish();
        }
        Err(error) => {
            error!(logger, "Unable to create stream"; "error" => ?error);
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

    let icy_muxer = Arc::new(IcyMuxer::new());
    let (response_sender, response_receiver) = mpsc::channel(512);

    icy_muxer.send_track_title(stream.track_title());

    let stream_source = match stream.get_output(&format) {
        Ok(stream_source) => stream_source,
        Err(error) => {
            error!(logger, "Unable to create stream"; "error" => ?error);
            return HttpResponse::ServiceUnavailable().finish();
        }
    };

    actix_rt::spawn({
        let mut stream_source = stream_source;
        let mut response_sender = response_sender;

        let icy_muxer = Arc::downgrade(&icy_muxer);

        async move {
            while let Some(event) = stream_source.next().await {
                match event {
                    StreamMessage::Buffer(buffer) => {
                        // Do not block encoder if one of the consumers are blocked due to network issues
                        match response_sender.try_send(buffer.bytes().clone()) {
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
                    StreamMessage::TrackTitle(title) => {
                        debug!(logger, "Received track title"; "title" => ?title);

                        if let Some(muxer) = icy_muxer.upgrade() {
                            muxer.send_track_title(title.title().to_string());
                        }
                    }
                }
            }
        }
    });

    {
        let channel_info = stream.channel_info();

        let mut response = HttpResponse::Ok();

        response.content_type(format.content_type).force_close();
        response.insert_header(("Accept-Ranges", "none"));

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

const SAMPLING_RATE: u32 = 48_000;

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

const START_BUFFER_TIME: Duration = Duration::from_millis(2500);

#[derive(Deserialize, Clone)]
pub struct GetChannelAudioStreamV3QueryParams {}

#[get("/v3/listen/{channel_id}")]
pub(crate) async fn get_channel_audio_stream_v3(
    request: HttpRequest,
    channel_id: web::Path<u64>,
    query_params: Query<GetChannelAudioStreamQueryParams>,
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
