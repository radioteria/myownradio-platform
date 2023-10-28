use crate::http_server::response::Response;
use actix_web::HttpResponse;

pub(crate) async fn handle_stream_started() -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn handle_stream_stats() -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn handle_stream_finished() -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn handle_stream_error() -> Response {
    Ok(HttpResponse::Ok().finish())
}
