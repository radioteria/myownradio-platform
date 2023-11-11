use crate::http_server::constants::{LEGACY_SESSION_COOKIE_NAME, YEAR};
use crate::http_server::response::Response;
use crate::services::auth::{
    Action, AuthService, AuthTokenService, LegacyLoginError, LegacyLogoutError,
    LegacyResetPasswordError, LegacySignupError, LegacySignupResult,
};
use actix_web::cookie::time::OffsetDateTime;
use actix_web::cookie::CookieBuilder;
use actix_web::{post, web, HttpRequest, HttpResponse};
use serde::Deserialize;
use serde_json::json;
use tracing::warn;

#[derive(Deserialize)]
pub(crate) struct LoginBody {
    pub(crate) email: String,
    pub(crate) password: String,
}

#[post("/v0/login")]
pub(crate) async fn login(
    body: web::Json<LoginBody>,
    auth_service: web::Data<AuthService>,
) -> Response {
    match auth_service.legacy_login(&body.email, &body.password).await {
        Ok((user, token)) => {
            let cookie = CookieBuilder::new(LEGACY_SESSION_COOKIE_NAME, token.clone())
                .expires(OffsetDateTime::now_utc() + YEAR)
                .finish();

            Ok(HttpResponse::Ok().cookie(cookie).json(user))
        }
        Err(LegacyLoginError::BadCredentials) => Ok(HttpResponse::Unauthorized().json(json!({
            "error": "BAD_CREDENTIALS"
        }))),
        Err(LegacyLoginError::DatabaseError(err)) => Err(err.into()),
        Err(LegacyLoginError::RepositoryError(err)) => Err(err.into()),
    }
}

#[post("/v0/logout")]
pub(crate) async fn logout(
    req: HttpRequest,
    auth_service: web::Data<AuthService>,
    token_service: web::Data<AuthTokenService>,
) -> Response {
    let cookie_value = match req.cookie(LEGACY_SESSION_COOKIE_NAME) {
        Some(cookie) => cookie.value().to_string(),
        None => {
            warn!("Missing {} cookie", LEGACY_SESSION_COOKIE_NAME);
            return Ok(HttpResponse::Unauthorized().json(json!({
                "error": "MISSING_COOKIE"
            })));
        }
    };

    let legacy_claims = match token_service.verify_legacy_claims(&cookie_value) {
        Some(claims) => claims,
        None => {
            warn!("Missing claims in {} cookie", LEGACY_SESSION_COOKIE_NAME);
            return Ok(HttpResponse::Unauthorized().json(json!({
                "error": "MISSING_CLAIMS_IN_COOKIE"
            })));
        }
    };

    match auth_service.legacy_logout(&legacy_claims.data.token).await {
        Ok(_) => {
            let cookie = CookieBuilder::new(LEGACY_SESSION_COOKIE_NAME, "")
                .expires(OffsetDateTime::now_utc())
                .finish();

            Ok(HttpResponse::NoContent().cookie(cookie).finish())
        }
        Err(LegacyLogoutError::DatabaseError(err)) => Err(err.into()),
        Err(LegacyLogoutError::RepositoryError(err)) => Err(err.into()),
    }
}

#[derive(Deserialize)]
pub(crate) struct SignupBody {
    pub(crate) email: String,
    pub(crate) password: String,
}

#[post("/v0/signup")]
pub(crate) async fn signup(
    body: web::Json<SignupBody>,
    auth_service: web::Data<AuthService>,
) -> Response {
    match auth_service
        .legacy_signup(&body.email, &body.password)
        .await
    {
        Ok(LegacySignupResult::SignedUp) => {
            Ok(HttpResponse::Ok().json(json!({ "result": "SIGNED_UP" })))
        }
        Ok(LegacySignupResult::ConfirmEmail) => {
            Ok(HttpResponse::Ok().json(json!({ "result": "CONFIRM_EMAIL" })))
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

#[post("/v0/confirm-email")]
pub(crate) async fn confirm_email() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

#[post("/v0/request-password-reset")]
pub(crate) async fn request_password_reset() -> Response {
    Ok(HttpResponse::NotImplemented().finish())
}

#[derive(Deserialize)]
pub(crate) struct ResetPasswordBody {
    pub(crate) action_token: String,
    pub(crate) old_password_hash: String,
    pub(crate) new_password: String,
}

#[post("/v0/reset-password")]
pub(crate) async fn reset_password(
    body: web::Json<ResetPasswordBody>,
    auth_service: web::Data<AuthService>,
    token_service: web::Data<AuthTokenService>,
) -> Response {
    let action_claims = match token_service.verify_action_claims(&body.action_token) {
        Some(claims) => claims,
        None => {
            warn!("Missing claims in action token");
            return Ok(HttpResponse::Unauthorized().json(json!({
                "error": "MISSING_CLAIMS_IN_COOKIE"
            })));
        }
    };

    if action_claims
        .actions
        .iter()
        .all(|action| !matches!(action, Action::ResetPassword))
    {
        warn!("Action not allowed in action claims");
        return Ok(HttpResponse::Unauthorized().json(json!({
            "error": "NO_PERMISSION"
        })));
    }

    match auth_service
        .legacy_reset_password(
            &action_claims.user_id,
            &body.new_password,
            &body.old_password_hash,
        )
        .await
    {
        Ok(()) => Ok(HttpResponse::NoContent().finish()),
        Err(LegacyResetPasswordError::DidNotUpdate) => Ok(HttpResponse::Conflict().json(json!({
            "error": "PASSWORD_DID_NOT_UPDATE"
        }))),
        Err(LegacyResetPasswordError::PasswordHashIsOutOfDate) => Ok(HttpResponse::BadRequest()
            .json(json!({
                "error": "PASSWORD_HASH_IS_OUT_OF_DATE"
            }))),
        Err(LegacyResetPasswordError::UserNotFound) => Ok(HttpResponse::BadRequest().json(json!({
            "error": "USER_NOT_FOUND"
        }))),
        Err(LegacyResetPasswordError::DatabaseError(err)) => Err(err.into()),
        Err(LegacyResetPasswordError::RepositoryError(err)) => Err(err.into()),
    }
}
