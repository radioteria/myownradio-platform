use crate::http_server::response::Response;
use actix_web::HttpResponse;

pub(crate) async fn login() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

pub(crate) async fn logout() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

pub(crate) async fn signup() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

pub(crate) async fn confirm_email() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

pub(crate) async fn request_password_reset() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

pub(crate) async fn reset_password() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}
