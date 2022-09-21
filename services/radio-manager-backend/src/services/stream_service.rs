use crate::data_structures::{StreamId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::RepositoryError;
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_stream_playlist_duration, seek_user_stream_forward,
    update_stream_status,
};
use crate::storage::db::repositories::user_stream_tracks::{
    get_single_stream_track_at_time_offset, get_stream_tracks, GetUserStreamTracksParams,
};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use crate::system::now;
use crate::MySqlClient;
use chrono::Duration;
use std::future::Future;
use std::ops::{DerefMut, Neg};
use tracing::debug;

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
            self.mysql_client.clone(),
        ))
    }
}

pub(crate) struct StreamService {
    stream_id: StreamId,
    mysql_client: MySqlClient,
}

impl StreamService {
    pub(crate) fn create(stream_id: StreamId, mysql_client: MySqlClient) -> Self {
        Self {
            stream_id,
            mysql_client,
        }
    }

    pub(crate) async fn play(&mut self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Playing,
            &Some(now()),
            &Some(0),
        )
        .await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn stop(&mut self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Stopped,
            &None,
            &None,
        )
        .await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn seek_forward(&mut self, time: Duration) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        seek_user_stream_forward(&mut connection, &self.stream_id, &time).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn seek_backward(&mut self, time: Duration) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        seek_user_stream_forward(&mut connection, &self.stream_id, &-time).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn play_next(&mut self) {}

    pub(crate) async fn play_prev(&mut self) {}

    pub(crate) async fn play_by_index(&mut self, index: u64) {}

    async fn notify_streams(&self) -> Result<(), StreamServiceError> {
        Ok(())
    }

    async fn update_stream_in_transaction<H, F>(
        &mut self,
        handler: H,
    ) -> Result<(), StreamServiceError>
    where
        H: FnOnce(&mut MySqlConnection) -> F + Future<Output = Result<(), StreamServiceError>>,
        F: Future<Output = Result<(), StreamServiceError>>,
    {
        let mut connection = self.mysql_client.transaction().await?;

        let stream_row = match get_single_stream_by_id(&mut connection, &self.stream_id).await? {
            Some(stream_row) => stream_row,
            None => return Err(StreamServiceError::NotFound),
        };

        let now_playing = match (
            &stream_row.status,
            &stream_row.started,
            &stream_row.started_from,
        ) {
            (StreamStatus::Playing, Some(started_at), Some(started_from)) => {
                let stream_time_position = now() - started_at + started_from;
                let playlist_duration =
                    get_stream_playlist_duration(&mut connection, &self.stream_id).await?;
                let playlist_time_position =
                    stream_time_position % playlist_duration.num_milliseconds();
                get_single_stream_track_at_time_offset(
                    &mut connection,
                    &self.stream_id,
                    &Duration::milliseconds(playlist_time_position),
                )
                .await?
            }
            _ => None,
        };

        debug!("Now playing track before transaction: {:?}", now_playing);

        handler(&mut connection).await?;

        if let Some((track, position)) = now_playing {
            let playlist_after_transaction = get_stream_tracks(
                &mut connection,
                &self.stream_id,
                &GetUserStreamTracksParams::default(),
                &0,
            )
            .await?;

            match playlist_after_transaction
                .iter()
                .find(|entry| entry.link.id == track.link.id)
            {
                Some(entry) => {
                    let offset_diff = track.link.time_offset - entry.link.time_offset;

                    debug!("Now playing track has changed time_offset: {}", offset_diff);

                    seek_user_stream_forward(
                        &mut connection,
                        &self.stream_id,
                        &Duration::milliseconds(offset_diff),
                    )
                    .await?;
                }
                None => {
                    debug!("Now playing track has been removed during transaction");

                    // User deleted track from playlist: starting next track
                    match playlist_after_transaction
                        .iter()
                        .find_or_first(|entry| entry.link.t_order == track.link.t_order)
                    {
                        Some(entry) => {
                            let offset_diff = track.link.time_offset
                                - entry.link.time_offset
                                - position.num_milliseconds();

                            debug!(
                                "Will start next track playing by changing offset: {}",
                                offset_diff
                            );

                            seek_user_stream_forward(
                                &mut connection,
                                &self.stream_id,
                                &Duration::milliseconds(offset_diff),
                            )
                            .await?;
                        }
                        None => {
                            debug!("Playlist seems to be empty: stopping the stream");

                            update_stream_status(
                                &mut connection,
                                &self.stream_id,
                                &StreamStatus::Stopped,
                                &None,
                                &None,
                            )
                            .await?;
                        }
                    }

                    self.notify_streams();
                }
            }
        }

        Ok(())
    }
}
