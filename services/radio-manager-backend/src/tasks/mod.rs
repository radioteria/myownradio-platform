use crate::storage::db::repositories::errors::RepositoryError;

pub(crate) mod streams;

#[derive(thiserror::Error, Debug)]
pub(crate) enum TaskError {
    #[error(transparent)]
    RepositoryError(#[from] RepositoryError),
    #[error("Stream is not playing")]
    StreamIsNotPlaying,
}

pub(crate) type TaskResult = Result<(), TaskError>;
