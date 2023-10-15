use actix_http::{BoxedPayloadStream, Payload};
use actix_web::error::ErrorBadRequest;
use actix_web::{FromRequest, HttpRequest};
use futures::future::{err, ok, Ready};
use std::ops::Deref;
use tracing::warn;

#[derive(Clone, Debug)]
pub(crate) struct UserId(i32);

impl Deref for UserId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl From<i32> for UserId {
    fn from(id: i32) -> Self {
        UserId(id)
    }
}

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
