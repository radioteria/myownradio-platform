use crate::config::Config;
use crate::k8s::K8sClient;
use crate::types::UserId;
use actix_server::Server;
use actix_web::{web, App, HttpResponse, HttpServer, Responder};
use tracing::{error, info};

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

    info!("{:?}", jobs);

    HttpResponse::Ok().finish()
}

pub(crate) async fn get_stream(
    path: web::Path<String>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    HttpResponse::Ok().finish()
}

pub(crate) async fn start_stream(
    path: web::Path<String>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    HttpResponse::Ok().finish()
}

pub(crate) async fn stop_stream(
    path: web::Path<String>,
    k8s_client: web::Data<K8sClient>,
    user_id: UserId,
) -> impl Responder {
    HttpResponse::Ok().finish()
}

pub(crate) fn run_server(config: &Config, k8s_client: &K8sClient) -> std::io::Result<Server> {
    let server = HttpServer::new({
        let k8s_client = k8s_client.clone();

        move || {
            App::new()
                .app_data(web::Data::new(k8s_client.clone()))
                .service(web::resource("/streams").route(web::get().to(get_streams)))
                .service(
                    web::resource("/streams/{id}")
                        .route(web::get().to(get_stream))
                        .route(web::post().to(start_stream))
                        .route(web::delete().to(start_stream)),
                )
        }
    });

    Ok(server.workers(2).bind(&config.bind_address)?.run())
}
