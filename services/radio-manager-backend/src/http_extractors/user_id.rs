use crate::models::types::UserId;
use actix_http::{BoxedPayloadStream, Payload};
use actix_web::error::ErrorBadRequest;
use actix_web::web::Data;
use actix_web::{FromRequest, HttpRequest};
use futures::future::{err, ok, Ready};
use slog::{warn, Logger};

impl FromRequest for UserId {
    type Error = actix_web::Error;
    type Future = Ready<Result<Self, Self::Error>>;

    fn from_request(req: &HttpRequest, _payload: &mut Payload<BoxedPayloadStream>) -> Self::Future {
        let logger = req
            .app_data::<Data<Logger>>()
            .expect("Unable to get logger from app data");

        ok(UserId::from(
            match req
                .headers()
                .get("user-id")
                .map(|header| header.to_str().map(|header| header.parse::<i32>()))
            {
                Some(Ok(Ok(header))) => header,
                _ => {
                    warn!(logger, "Bad request: no user-id header");
                    return err(ErrorBadRequest(String::new()));
                }
            },
        ))
    }
}
