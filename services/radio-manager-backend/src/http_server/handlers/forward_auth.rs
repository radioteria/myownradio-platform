use crate::http_server::response::Response;
use crate::services::auth::{AuthTokenService, IsActionAllowed};
use actix_web::{web, HttpRequest, HttpResponse};
use tracing::warn;

pub(crate) async fn auth_by_jwt_token(
    req: HttpRequest,
    auth_token_service: web::Data<AuthTokenService>,
) -> Response {
    let auth_str = match req
        .headers()
        .get("Authorization")
        .and_then(|value| value.to_str().ok())
    {
        Some(auth_header) => auth_header,
        None => {
            warn!("Missing Authorization header");
            return Ok(HttpResponse::Unauthorized().finish());
        }
    };

    let token = match auth_str.starts_with("Bearer ") {
        true => &auth_str[7..],
        false => {
            warn!("Only Bearer authorization currently supported");
            return Ok(HttpResponse::Unauthorized().finish());
        }
    };

    let forwarded_method = match req
        .headers()
        .get("X-Forwarded-Method")
        .and_then(|value| value.to_str().ok())
    {
        Some(auth_header) => auth_header,
        None => {
            warn!("Missing X-Forwarded-Method header");
            return Ok(HttpResponse::Unauthorized().finish());
        }
    };

    let forwarded_uri = match req
        .headers()
        .get("X-Forwarded-Uri")
        .and_then(|value| value.to_str().ok())
    {
        Some(auth_header) => auth_header,
        None => {
            warn!("Missing X-Forwarded-Uri header");
            return Ok(HttpResponse::Unauthorized().finish());
        }
    };

    let claims = match auth_token_service.verify_claims(token) {
        Some(claims) => claims,
        None => {
            warn!("Missing claims in token");
            return Ok(HttpResponse::Unauthorized().finish());
        }
    };

    if claims.is_action_allowed(forwarded_method, forwarded_uri) {
        return Ok(HttpResponse::Ok()
            .insert_header(("User-Id", format!("{}", *claims.user_id)))
            .body("Ok 👍"));
    }

    Ok(HttpResponse::Unauthorized().finish())
}
