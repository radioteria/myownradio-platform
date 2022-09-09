#[derive(thiserror::Error, Debug)]
pub(crate) enum RepositoryError {
    #[error(transparent)]
    DatabaseError(#[from] sqlx::Error),
}

pub(crate) type RepositoryResult<T> = Result<T, RepositoryError>;
