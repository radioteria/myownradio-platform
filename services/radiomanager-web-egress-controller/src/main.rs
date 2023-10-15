use crate::config::Config;
use crate::http::run_server;
use crate::k8s::K8sClient;

mod config;
mod http;
mod k8s;
mod types;

#[actix_rt::main]
pub(crate) async fn main() -> std::io::Result<()> {
    tracing_subscriber::fmt::init();

    let config = Config::from_env();

    let k8s_client = K8sClient::create(
        &config.egress_namespace,
        &config.egress_image_name,
        &config.egress_image_tag,
    )
    .await
    .expect("Unable to initialize k8s client");

    let http_server = run_server(&config, &k8s_client).expect("Unable to start HTTP server");

    http_server.await
}
