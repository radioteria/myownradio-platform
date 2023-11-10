use crate::http_server::constants::LEGACY_SESSION_COOKIE_NAME;
use crate::http_server::response::Response;
use crate::services::auth::{AuthService, LegacyLoginError, LegacySignupError, LegacySignupResult};
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
    auth_service: web::Data<AuthService>,
) -> Response {
    match auth_service.legacy_login(&body.email, &body.password).await {
        Ok((user, token)) => {
            let cookie = CookieBuilder::new(LEGACY_SESSION_COOKIE_NAME, token.clone()).finish();

            Ok(HttpResponse::Ok().cookie(cookie).json(user))
        }
        Err(LegacyLoginError::BadCredentials) => Ok(HttpResponse::Unauthorized().json(json!({
            "error": "BAD_CREDENTIALS"
        }))),
        Err(LegacyLoginError::DatabaseError(err)) => Err(err.into()),
        Err(LegacyLoginError::RepositoryError(err)) => Err(err.into()),
    }
}

pub(crate) async fn logout() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

#[derive(Deserialize)]
pub(crate) struct SignupBody {
    pub(crate) email: String,
    pub(crate) password: String,
}

pub(crate) async fn signup(
    body: web::Json<SignupBody>,
    auth_service: web::Data<AuthService>,
) -> Response {
    match auth_service
        .legacy_signup(&body.email, &body.password)
        .await
    {
        Ok(LegacySignupResult::SignedUp) => {
            Ok(HttpResponse::Ok().json(json!({ "result": "SignedUp" })))
        }
        Ok(LegacySignupResult::ConfirmEmail) => {
            Ok(HttpResponse::Ok().json(json!({ "result": "ConfirmEmail" })))
        }
        Err(LegacySignupError::InvalidEmailAddress | LegacySignupError::InvalidPassword) => {
            Ok(HttpResponse::BadRequest().json(json!({ "error": "BAD_CREDENTIALS" })))
        }
        Err(LegacySignupError::NonUniqueEmailAddress) => {
            Ok(HttpResponse::Conflict().json(json!({ "error": "NON_UNIQUE_EMAIL_ADDRESS" })))
        }
        Err(LegacySignupError::DatabaseError(err)) => Err(err.into()),
        Err(LegacySignupError::RepositoryError(err)) => Err(err.into()),
    }
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
