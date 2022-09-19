use crate::data_structures::UserId;
use actix_http::{BoxedPayloadStream, Payload};
use actix_web::error::ErrorBadRequest;
use actix_web::{FromRequest, HttpRequest};
use futures::future::{err, ok, Ready};
use tracing::warn;

impl FromRequest for UserId {
    type Error = actix_web::Error;
    type Future = Ready<Result<Self, Self::Error>>;

    fn from_request(req: &HttpRequest, _payload: &mut Payload<BoxedPayloadStream>) -> Self::Future {
        ok(UserId::from(
            match req.headers().get("user-id").and_then(|header| {
                header
                    .to_str()
                    .ok()
                    .and_then(|header| header.parse::<i32>().ok())
            }) {
                Some(user_id) => user_id,
                _ => {
                    warn!("Bad request: no user-id header");
                    return err(ErrorBadRequest(String::new()));
                }
            },
        ))
    }
}
