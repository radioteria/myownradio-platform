use crate::config::Config;
use crate::k8s::K8sClient;
use crate::types::UserId;
use actix_server::Server;
use actix_web::{web, App, HttpResponse, HttpServer, Responder};
use k8s_openapi::serde_json;
use serde::Deserialize;
use tracing::error;

pub(crate) async fn get_streams(
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    let jobs = match k8s_client.get_stream_jobs_by_user(&user_id).await {
        Ok(jobs) => jobs,
        Err(error) => {
            error!("{}", error);
            return HttpResponse::InternalServerError().finish();
        }
    };

    HttpResponse::Ok().json(serde_json::json!(jobs
        .into_iter()
        .map(|stream| stream.name)
        .collect::<Vec<_>>()))
}

pub(crate) async fn get_stream(
    path: web::Path<String>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    let stream_id = path.into_inner();

    match k8s_client.get_stream_job(&stream_id, &user_id).await {
        Ok(Some(stream)) => HttpResponse::Ok().json(serde_json::json!({ "stream": stream.name })),
        Ok(None) => HttpResponse::NotFound().body("no_stream"),
        Err(error) => {
            error!("{}", error);
            HttpResponse::InternalServerError().finish()
        }
    }
}

#[derive(Deserialize)]
pub(crate) struct StartStreamRequestBody {
    stream_id: String,
    website_url: String,
    rtmp_url: String,
    rtmp_stream_key: String,
}

pub(crate) async fn start_stream(
    body: web::Json<StartStreamRequestBody>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    if let Err(error) = k8s_client
        .create_stream_job(
            &body.stream_id,
            &user_id,
            &body.website_url,
            &body.rtmp_url,
            &body.rtmp_stream_key,
        )
        .await
    {
        error!("{}", error);
        return HttpResponse::InternalServerError().finish();
    }

    HttpResponse::Ok().finish()
}

pub(crate) async fn stop_stream(
    path: web::Path<String>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    let stream_id = path.into_inner();

    if let Err(error) = k8s_client.delete_stream_job(&stream_id, &user_id).await {
        error!("{}", error);
        return HttpResponse::InternalServerError().finish();
    }

    HttpResponse::Ok().finish()
}

pub(crate) fn run_server(config: &Config, k8s_client: &K8sClient) -> std::io::Result<Server> {
    let server = HttpServer::new({
        let k8s_client = k8s_client.clone();

        move || {
            App::new()
                .app_data(web::Data::new(k8s_client.clone()))
                .service(
                    web::resource("/streams")
                        .route(web::get().to(get_streams))
                        .route(web::post().to(start_stream)),
                )
                .service(
                    web::resource("/streams/{id}")
                        .route(web::get().to(get_stream))
                        .route(web::delete().to(stop_stream)),
                )
        }
    });

    Ok(server.workers(2).bind(&config.bind_address)?.run())
}
