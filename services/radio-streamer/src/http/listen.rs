use crate::audio_decoder::AudioDecoder;
use crate::mor_backend_client::MorBackendClient;
use actix_web::http::header::ContentType;
use actix_web::web::Data;
use actix_web::{get, web, HttpResponse, Responder};
use std::sync::Arc;

#[get("/listen/{channel_id}")]
pub async fn listen_by_channel_id(
    channel_id: web::Path<u32>,
    mor_backend_client: Data<Arc<MorBackendClient>>,
    audio_decoder: Data<Arc<AudioDecoder>>,
) -> impl Responder {
    let now_playing = match mor_backend_client.get_now_playing(&channel_id).await {
        Ok(now_playing) => now_playing,
        Err(_) => return HttpResponse::ServiceUnavailable().finish(),
    };

    HttpResponse::Ok()
        .content_type("audio/mp3")
        .streaming(audio_decoder.decode_audio_file(&now_playing.current_track.url))
}
