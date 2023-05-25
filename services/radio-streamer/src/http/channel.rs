use super::utils::icy_muxer::{IcyMuxer, ICY_METADATA_INTERVAL};
use crate::audio_formats::{AudioFormat, AudioFormats};
use crate::backend_client::{BackendClient, GetChannelInfoError, GetNowPlayingError, NowPlaying};
use crate::config::Config;
use crate::stream::{StreamCreateError, StreamMessage, StreamsRegistry, StreamsRegistryExt};
use actix_web::web::{Bytes, Data, Query};
use actix_web::{get, web, HttpRequest, HttpResponse, Responder};
use futures::channel::mpsc;
use futures::executor::{block_on, LocalSpawner};
use futures::StreamExt;
use myownradio_ffmpeg_utils::OutputFormat;
use myownradio_player_loop::{NowPlayingClient, NowPlayingError, NowPlayingResponse, PlayerLoop};
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

    stream_registry.restart_stream(&channel_id);

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

// impl NowPlayingResponse for NowPlaying {
//     fn curr_url(&self) -> &str {
//         &self.current_track.url
//     }
//
//     fn curr_title(&self) -> &str {
//         &self.current_track.title
//     }
//
//     fn curr_duration(&self) -> &Duration {
//         &self.current_track.duration
//     }
//
//     fn curr_position(&self) -> &Duration {
//         &self.current_track.offset
//     }
// }
//
// impl NowPlayingError for GetNowPlayingError {}
//
// impl NowPlayingClient for BackendClient {
//     fn get_now_playing(
//         &self,
//         channel_id: &u32,
//         time: &SystemTime,
//     ) -> Result<Box<dyn NowPlayingResponse>, Box<dyn NowPlayingError>> {
//         let runtime = actix_rt::Runtime::new().expect("Unable to create Runtime");
//
//         let channel_id = *channel_id as usize;
//
//         let future = BackendClient::get_now_playing(self, &channel_id, time);
//
//         runtime
//             .block_on(future)
//             .map(|value| Box::new(value) as Box<dyn NowPlayingResponse>)
//             .map_err(|error| Box::new(error) as Box<dyn NowPlayingError>)
//     }
// }

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

#[get("/v3/listen/{channel_id}")]
pub(crate) async fn get_channel_audio_stream_v3(
    request: HttpRequest,
    channel_id: web::Path<u32>,
    query_params: Query<GetChannelAudioStreamQueryParams>,
    logger: Data<Arc<Logger>>,
    client: Data<Arc<BackendClient>>,
) -> impl Responder {
    let channel_id = channel_id.into_inner();
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
    let initial_time = SystemTime::now() - START_BUFFER_TIME;
    let content_type = format.content_type;

    let (response_sender, response_receiver) = mpsc::channel(512);
    let icy_muxer = Arc::new(IcyMuxer::new());

    std::thread::spawn({
        let mut response_sender = response_sender;
        let icy_muxer = icy_muxer.clone();

        move || {
            let result = PlayerLoop::create(
                channel_id,
                BackendClient::clone(&client),
                format.into(),
                initial_time.clone(),
            );

            let mut player_loop = match result {
                Ok(player_loop) => player_loop,
                Err(error) => {
                    return;
                }
            };

            let mut previous_title = String::new();

            while let Ok(packets) = player_loop.receive_next_audio_packets() {
                if let Some(title) = player_loop.current_title() {
                    if title != &previous_title {
                        icy_muxer.send_track_title(title.to_string());
                        previous_title = title.to_string();
                    }
                }

                for packet in packets {
                    let bytes = Bytes::copy_from_slice(&packet.data());
                    match response_sender.try_send(bytes) {
                        Err(error) if error.is_disconnected() => {
                            // Disconnected: drop the receiver
                            return;
                        }
                        Err(error) if error.is_full() => {
                            // Buffer is full: skip the remaining bytes
                        }
                        Err(error) => {
                            warn!(logger, "Unable to send bytes to the client"; "error" => ?error);
                            // Unexpected error: drop the receiver
                            return;
                        }
                        Ok(_) => (),
                    }

                    let sleep_dur = (initial_time + packet.pts_as_duration())
                        .duration_since(SystemTime::now())
                        .ok();

                    if let Some(dur) = sleep_dur {
                        std::thread::sleep(dur);
                    }
                }
            }
        }
    });

    let mut response = HttpResponse::Ok();

    response.content_type(content_type).force_close();

    if is_icy_enabled {
        response
            .insert_header(("icy-metadata", "1"))
            .insert_header(("icy-metaint", format!("{}", ICY_METADATA_INTERVAL)));
        // .insert_header(("icy-name", "todo"));

        response.streaming::<_, actix_web::Error>(
            response_receiver
                .map(move |bytes| icy_muxer.handle_bytes(bytes))
                .map(Ok),
        )
    } else {
        response.streaming::<_, actix_web::Error>(response_receiver.map(Ok))
    }
}
