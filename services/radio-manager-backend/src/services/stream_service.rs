use crate::data_structures::{StreamId, UserId};
use crate::storage::db::repositories::errors::RepositoryError;
use crate::storage::db::repositories::streams::{get_single_stream_by_id, update_stream};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use crate::system::now;
use crate::MySqlClient;
use chrono::Duration;

#[derive(thiserror::Error, Debug)]
pub(crate) enum StreamServiceError {
    #[error("No permission to access this stream")]
    Forbidden,
    #[error("Stream does not exist")]
    NotFound,
    #[error("Stream has unexpected state")]
    UnexpectedState,
    #[error("Repository error: {0}")]
    RepositoryError(#[from] RepositoryError),
    #[error("Database error: {0}")]
    DatabaseError(#[from] sqlx::Error),
}

pub(crate) struct StreamServiceFactory {
    mysql_client: MySqlClient,
}

impl StreamServiceFactory {
    pub(crate) async fn create_service(
        &self,
        stream_id: &StreamId,
        user_id: &UserId,
    ) -> Result<StreamService, StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        let stream_row = get_single_stream_by_id(&mut connection, stream_id).await?;
        drop(connection);

        let stream_row = match stream_row {
            Some(stream_row) => stream_row,
            None => return Err(StreamServiceError::NotFound),
        };

        if &stream_row.uid != user_id {
            return Err(StreamServiceError::Forbidden);
        }

        Ok(StreamService::create(
            stream_id.clone(),
            stream_row,
            self.mysql_client.clone(),
        ))
    }
}

pub(crate) struct StreamService {
    stream_id: StreamId,
    stream_row: StreamRow,
    mysql_client: MySqlClient,
}

impl StreamService {
    pub(crate) fn create(
        stream_id: StreamId,
        stream_row: StreamRow,
        mysql_client: MySqlClient,
    ) -> Self {
        Self {
            stream_id,
            stream_row,
            mysql_client,
        }
    }

    async fn notify_streaming(&self) -> Result<(), StreamServiceError> {
        Ok(())
    }

    pub(crate) async fn play(&mut self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.stream_row.status = StreamStatus::Playing;
        self.stream_row.started_from = Some(0);
        self.stream_row.started = Some(now());
        update_stream(&mut connection, &self.stream_row).await?;
        drop(connection);

        self.notify_streaming();

        Ok(())
    }

    pub(crate) async fn stop(&mut self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.stream_row.status = StreamStatus::Stopped;
        self.stream_row.started_from = None;
        self.stream_row.started = None;
        update_stream(&mut connection, &self.stream_row).await?;
        drop(connection);

        self.notify_streaming();

        Ok(())
    }

    pub(crate) async fn seek_forward(&mut self, time: Duration) {}

    pub(crate) async fn seek_backward(&mut self, time: Duration) {}

    pub(crate) async fn play_next(&mut self) {}

    pub(crate) async fn play_prev(&mut self) {}

    pub(crate) async fn play_by_index(&mut self, index: u64) {}
}
