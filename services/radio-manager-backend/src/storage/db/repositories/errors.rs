pub(crate) enum RepositoryError {
    DatabaseError(sqlx::Error),
}

pub(crate) type RepositoryResult<T> = Result<T, RepositoryError>;
