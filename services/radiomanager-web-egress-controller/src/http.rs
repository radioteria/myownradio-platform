use crate::config::Config;
use crate::k8s::{K8sClient, K8sClientError};
use crate::types::{AudioSettings, RtmpSettings, UserId, VideoSettings};
use actix_server::Server;
use actix_web::{web, App, HttpResponse, HttpServer, Responder};
use k8s_openapi::serde_json::json;
use kube::Error;
use serde::Deserialize;
use tracing::error;

const STREAM_NOT_FOUND: &str = "STREAM_NOT_FOUND";

pub(crate) async fn get_streams(
    path: web::Path<UserId>,
    k8s_client: web::Data<K8sClient>,
) -> impl Responder {
    let user_id = path.into_inner();

    let jobs = match k8s_client.get_stream_jobs_by_user(&user_id).await {
        Ok(jobs) => jobs,
        Err(error) => {
            error!("{}", error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(jobs)
}

pub(crate) async fn get_stream(
    path: web::Path<(UserId, u32)>,
    k8s_client: web::Data<K8sClient>,
) -> impl Responder {
    let (user_id, channel_id) = path.into_inner();

    match k8s_client.get_stream_job(&user_id, &channel_id).await {
        Ok(stream) => HttpResponse::Ok().json(stream),
        Err(K8sClientError::KubeClient(Error::Api(res))) if res.code == 404 => {
            HttpResponse::NotFound().json(json!({ "error": STREAM_NOT_FOUND }))
        }
        Err(error) => {
            error!("{}", error);
            HttpResponse::InternalServerError().finish()
        }
    }
}

#[derive(Deserialize)]
pub(crate) struct StartStreamRequestBody {
    stream_id: String,
    channel_id: u32,
    webpage_url: String,
    rtmp_settings: RtmpSettings,
    video_settings: VideoSettings,
    audio_settings: AudioSettings,
}

pub(crate) async fn start_stream(
    body: web::Json<StartStreamRequestBody>,
    k8s_client: web::Data<K8sClient>,
    path: web::Path<UserId>,
) -> impl Responder {
    let user_id = path.into_inner();

    if let Err(error) = k8s_client
        .create_stream_job(
            &user_id,
            &body.stream_id,
            &body.channel_id,
            &body.webpage_url,
            &body.rtmp_settings,
            &body.video_settings,
            &body.audio_settings,
        )
        .await
    {
        error!("{}", error);
        return HttpResponse::InternalServerError().finish();
    }

    HttpResponse::Ok().finish()
}

pub(crate) async fn stop_stream(
    path: web::Path<(UserId, u32)>,
    k8s_client: web::Data<K8sClient>,
) -> impl Responder {
    let (user_id, channel_id) = path.into_inner();

    let result = match k8s_client.delete_stream_job(&user_id, &channel_id).await {
        Ok(result) => result,
        Err(K8sClientError::KubeClient(Error::Api(res))) if res.code == 404 => {
            return HttpResponse::Conflict().json(json!({ "error": STREAM_NOT_FOUND }));
        }
        Err(error) => {
            error!("{}", error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    if result {
        HttpResponse::Ok().finish()
    } else {
        HttpResponse::Conflict().finish()
    }
}

pub(crate) fn run_server(config: &Config, k8s_client: &K8sClient) -> std::io::Result<Server> {
    let server = HttpServer::new({
        let k8s_client = k8s_client.clone();

        move || {
            App::new()
                .app_data(web::Data::new(k8s_client.clone()))
                .service(
                    web::resource("/users/{user_id}/streams")
                        .route(web::get().to(get_streams))
                        .route(web::post().to(start_stream)),
                )
                .service(
                    web::resource("/users/{user_id}/streams/{channel_id}")
                        .route(web::get().to(get_stream))
                        .route(web::delete().to(stop_stream)),
                )
        }
    });

    Ok(server.workers(2).bind(&config.bind_address)?.run())
}
