use crate::http_server::constants::LEGACY_SESSION_COOKIE_NAME;
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::services::auth::{AuthTokenService, LegacyAuthTokenClaims, LegacyAuthTokenData};
use crate::storage::db::repositories::{legacy_sessions, users};
use crate::utils::verify_password;
use actix_web::cookie::CookieBuilder;
use actix_web::{web, HttpResponse};
use serde::Deserialize;
use serde_json::json;

#[derive(Deserialize)]
pub(crate) struct LoginBody {
    pub(crate) email: String,
    pub(crate) password: String,
}

pub(crate) async fn login(
    body: web::Json<LoginBody>,
    mysql_client: web::Data<MySqlClient>,
    token_service: web::Data<AuthTokenService>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    let user = match users::get_user_by_email(&mut connection, &body.email).await? {
        Some(user) => {
            let hashed_password = user.password.clone().unwrap_or_default();
            let is_valid = verify_password(&body.password, &hashed_password)
                .expect("Unable to verify password");

            if !is_valid {
                return Ok(HttpResponse::Unauthorized().json(json!({
                    "error": "BAD_CREDENTIALS"
                })));
            }

            user
        }
        None => {
            return Ok(HttpResponse::Unauthorized().json(json!({
                "error": "BAD_CREDENTIALS"
            })));
        }
    };

    let legacy_session = legacy_sessions::create_legacy_session(&mut connection, &user.uid).await?;

    let token = token_service.sign_legacy_claims(LegacyAuthTokenClaims {
        id: legacy_session.session_id,
        data: LegacyAuthTokenData {
            token: legacy_session.token,
        },
    });

    let cookie = CookieBuilder::new(LEGACY_SESSION_COOKIE_NAME, token).finish();

    Ok(HttpResponse::Ok().cookie(cookie).json(json!({
        "id": user.uid,
        "email": user.mail
    })))
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
