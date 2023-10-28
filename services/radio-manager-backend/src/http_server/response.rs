use crate::pubsub_client::PubsubClientError;
use crate::services::StreamServiceError;
use crate::storage::db::repositories::errors::RepositoryError;
use crate::web_egress_controller_client::WebEgressControllerError;
use actix_http::StatusCode;
use actix_web::{HttpResponse, ResponseError};
use serde::Serialize;
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

#[derive(Serialize)]
struct ErrorResponse {
    error: &'static str,
}

impl ResponseError for Error {
    fn status_code(&self) -> StatusCode {
        match self {
            Error::DatabaseError(_) => StatusCode::INTERNAL_SERVER_ERROR,
            Error::RepositoryError(_) => StatusCode::INTERNAL_SERVER_ERROR,
            Error::IOError(_) => StatusCode::INTERNAL_SERVER_ERROR,
            Error::StreamServiceError(_) => StatusCode::INTERNAL_SERVER_ERROR,
            Error::WebEgressControllerClientError(_) => StatusCode::INTERNAL_SERVER_ERROR,
            Error::PubsubClientError(_) => StatusCode::INTERNAL_SERVER_ERROR,
        }
    }

    fn error_response(&self) -> HttpResponse {
        error!(?self, "Error response");

        match self {
            Error::DatabaseError(_) => {
                HttpResponse::build(self.status_code()).json(ErrorResponse {
                    error: "database_error",
                })
            }
            Error::RepositoryError(_) => {
                HttpResponse::build(self.status_code()).json(ErrorResponse {
                    error: "repository_error",
                })
            }
            Error::IOError(_) => {
                HttpResponse::build(self.status_code()).json(ErrorResponse { error: "io_error" })
            }
            Error::StreamServiceError(_) => {
                HttpResponse::build(self.status_code()).json(ErrorResponse {
                    error: "stream_service_error",
                })
            }
            Error::WebEgressControllerClientError(_) => HttpResponse::build(self.status_code())
                .json(ErrorResponse {
                    error: "web_egress_service_error",
                }),
            Error::PubsubClientError(_) => {
                HttpResponse::build(self.status_code()).json(ErrorResponse {
                    error: "pubsub_client_error",
                })
            }
        }
    }
}
