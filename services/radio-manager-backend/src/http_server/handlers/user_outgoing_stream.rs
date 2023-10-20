use crate::http_server::response::Response;
use actix_web::HttpResponse;

pub(crate) async fn get_outgoing_stream() -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn start_outgoing_stream() -> Response {
    Ok(HttpResponse::Ok().finish())
}

pub(crate) async fn stop_outgoing_stream() -> Response {
    Ok(HttpResponse::Ok().finish())
}
