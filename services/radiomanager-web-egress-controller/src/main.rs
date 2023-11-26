use crate::backend_client::BackendClient;
use crate::config::Config;
use crate::http::run_server;
use crate::k8s::K8sClient;

mod backend_client;
mod config;
mod http;
mod k8s;
mod k8s_utils;
mod stream_events;
mod types;

#[actix_rt::main]
pub(crate) async fn main() -> std::io::Result<()> {
    tracing_subscriber::fmt::init();

    let config = Config::from_env();

    let backend_client = BackendClient::create(&config.radiomanager_backend.endpoint);
    let k8s_client = K8sClient::create(
        &config.egress_namespace,
        &config.egress_image_name,
        &config.egress_image_tag,
        &config.radiomanager_backend.endpoint,
        &backend_client,
    )
    .await
    .expect("Unable to initialize k8s client");

    let http_server =
        run_server(&config, &k8s_client, &backend_client).expect("Unable to start HTTP server");

    http_server.await
}
