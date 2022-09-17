use crate::storage::db::repositories::errors::RepositoryError;

mod streams;

#[derive(thiserror::Error)]
pub(crate) enum TaskError {
    #[error(transparent)]
    RepositoryError(#[from] RepositoryError),
}

pub(crate) type TaskResult = Result<(), TaskError>;
