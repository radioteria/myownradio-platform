use crate::config::Config;
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::services::auth::{AuthTokenService, IsActionAllowed};
use crate::storage::db::repositories::users::get_user_by_session_token;
use actix_web::{web, HttpRequest, HttpResponse};
use tracing::{debug, warn};

const LEGACY_SESSION_COOKIE_NAME: &str = "secure_session";

pub(crate) async fn auth_by_jwt_token_or_legacy_token(
    req: HttpRequest,
    auth_token_service: web::Data<AuthTokenService>,
    mysql_client: web::Data<MySqlClient>,
) -> Response {
    debug!("Headers {:?}", req.headers());

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

    Ok(
        match req
            .headers()
            .get("Authorization")
            .and_then(|value| value.to_str().ok())
        {
            Some(auth_str) => {
                let token = match auth_str.starts_with("Bearer ") {
                    true => &auth_str[7..],
                    false => {
                        warn!("Only Bearer authorization currently supported");
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
                    HttpResponse::Ok()
                        .insert_header(("User-Id", format!("{}", *claims.user_id)))
                        .body("Ok 👍")
                } else {
                    HttpResponse::Unauthorized().finish()
                }
            }
            None => {
                let cookie_value = match req.cookie(LEGACY_SESSION_COOKIE_NAME) {
                    Some(cookie) => cookie.value().to_string(),
                    None => {
                        warn!("Missing {} cookie", LEGACY_SESSION_COOKIE_NAME);
                        return Ok(HttpResponse::Unauthorized().finish());
                    }
                };

                let legacy_claims = match auth_token_service.verify_legacy_claims(&cookie_value) {
                    Some(claims) => claims,
                    None => {
                        warn!("Missing claims in legacy token");
                        return Ok(HttpResponse::Unauthorized().finish());
                    }
                };

                let mut connection = mysql_client.connection().await?;

                let maybe_user =
                    get_user_by_session_token(&mut connection, &legacy_claims.data.token).await?;

                match maybe_user {
                    Some(user) => HttpResponse::Ok()
                        .insert_header(("User-Id", format!("{}", *user.uid)))
                        .body("Ok 👍"),
                    None => {
                        warn!("Missing user associated with legacy token");
                        HttpResponse::Unauthorized().finish()
                    }
                }
            }
        },
    )
}
