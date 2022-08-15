use actix_web::{HttpResponse, Responder};

pub(crate) async fn skip_current_track() -> impl Responder {
    HttpResponse::Ok().finish()
}
