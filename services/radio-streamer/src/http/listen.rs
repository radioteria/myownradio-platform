use crate::audio_decoder::AudioDecoder;
use crate::audio_encoder::AudioEncoder;
use crate::mor_backend_client::MorBackendClient;
use actix_web::web::Data;
use actix_web::{get, web, HttpResponse, Responder};
use futures::{SinkExt, StreamExt};
use slog::{debug, error, Logger};
use std::sync::Arc;

#[get("/listen/{channel_id}")]
pub async fn listen_by_channel_id(
    channel_id: web::Path<u32>,
    mor_backend_client: Data<Arc<MorBackendClient>>,
    audio_decoder: Data<Arc<AudioDecoder>>,
    audio_encoder: Data<Arc<AudioEncoder>>,
    logger: Data<Arc<Logger>>,
) -> impl Responder {
    let (enc_sender, enc_receiver) = match audio_encoder.make_encoder() {
        Ok(ok) => ok,
        Err(error) => {
            error!(logger, "Unable to start audio encoder"; "error" => ?error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    actix_rt::spawn({
        let mut enc_sender = enc_sender;
        let logger = logger.clone();

        async move {
            loop {
                let now_playing = match mor_backend_client.get_now_playing(&channel_id).await {
                    Ok(now_playing) => {
                        debug!(logger, "Now playing: {:?}", &now_playing);
                        now_playing
                    }
                    Err(_) => {
                        // error!(logger, "Unable to get now playing"; "error" => &error);
                        return;
                    }
                };

                let mut dec_receiver = match audio_decoder.decode_audio_file(
                    &now_playing.current_track.url,
                    &now_playing.current_track.offset,
                ) {
                    Ok(receiver) => receiver,
                    Err(error) => {
                        error!(logger, "Unable to decode audio file"; "error" => ?error);
                        return;
                    }
                };

                while let Some(r) = dec_receiver.next().await {
                    if let Err(error) = enc_sender.send(r).await {
                        error!(logger, "Unable to pipe bytes"; "error" => ?error);
                        return;
                    }
                }
            }
        }
    });

    HttpResponse::Ok()
        .content_type("audio/mp3")
        .force_close()
        .streaming(enc_receiver)
}
