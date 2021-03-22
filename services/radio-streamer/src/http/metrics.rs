use crate::metrics::Metrics;
use actix_web::web::Data;
use actix_web::{get, HttpResponse, Responder};
use std::sync::Arc;

#[get("/metrics")]
pub async fn get_metrics(metrics: Data<Arc<Metrics>>) -> impl Responder {
    HttpResponse::Ok()
        .content_type("text/plain")
        .force_close()
        .body(metrics.gather())
}
