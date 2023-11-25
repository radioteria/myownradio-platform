use crate::pubsub_client::PubsubClientError;
use crate::services::StreamServiceError;
use crate::storage::db::repositories::errors::RepositoryError;
use crate::web_egress_controller_client::WebEgressControllerError;
use actix_http::StatusCode;
use actix_web::{HttpResponse, ResponseError};
use serde_json::json;
use std::fmt::Debug;
use tracing::error;

pub(crate) type Response = Result<HttpResponse, Error>;

#[derive(thiserror::Error, Debug)]
pub(crate) enum Error {
    #[error("Error running SQL query: {0}")]
    DatabaseError(#[from] sqlx::Error),
    #[error("Repository error: {0}")]
    RepositoryError(#[from] RepositoryError),
    #[error("IO error: {0}")]
    IOError(#[from] std::io::Error),
    #[error("StreamServiceError: {0}")]
    StreamServiceError(#[from] StreamServiceError),
    #[error("WebEgressControllerClientError: {0}")]
    WebEgressControllerClientError(#[from] WebEgressControllerError),
    #[error("PubsubClientError: {0}")]
    PubsubClientError(#[from] PubsubClientError),
}

impl ResponseError for Error {
    fn status_code(&self) -> StatusCode {
        match self {
            _ => StatusCode::INTERNAL_SERVER_ERROR,
        }
    }

    fn error_response(&self) -> HttpResponse {
        error!(?self, "ResponseError");

        match self {
            _ => HttpResponse::build(self.status_code()).json(json!({
                "error": "INTERNAL_SERVER_ERROR",
            })),
        }
    }
}
