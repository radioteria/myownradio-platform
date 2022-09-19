use crate::storage::db::repositories::errors::RepositoryError;
use crate::tasks::TaskError;
use actix_http::StatusCode;
use actix_web::{HttpResponse, ResponseError};
use serde::Serialize;
use std::fmt::Debug;

pub(crate) type Response = Result<HttpResponse, Error>;

#[derive(thiserror::Error, Debug)]
pub(crate) enum Error {
    #[error("Error running SQL query: {0}")]
    DatabaseError(#[from] sqlx::Error),
    #[error("Repository error: {0}")]
    RepositoryError(#[from] RepositoryError),
    #[error("IO error: {0}")]
    IOError(#[from] std::io::Error),
    #[error("Task error: {0}")]
    TaskError(#[from] TaskError),
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
            Error::TaskError(_) => StatusCode::INTERNAL_SERVER_ERROR,
        }
    }

    fn error_response(&self) -> HttpResponse {
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
            Error::TaskError(_) => HttpResponse::build(self.status_code()).json(ErrorResponse {
                error: "task_error",
            }),
        }
    }
}
