use actix_web::{get, web, HttpResponse, Responder};

#[get("/listen/{channel_id}")]
pub async fn listen(channel_id: web::Path<u32>) -> impl Responder {
    HttpResponse::NotImplemented().body(format!("{} not implemented!", channel_id))
}
