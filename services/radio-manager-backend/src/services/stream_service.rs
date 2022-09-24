use crate::config::RadioStreamerConfig;
use crate::data_structures::{LinkId, OrderId, StreamId, TrackId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::storage::db::repositories::errors::{RepositoryError, RepositoryResult};
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, get_stream_playlist_duration, seek_user_stream_forward,
    update_stream_status,
};
use crate::storage::db::repositories::user_stream_tracks::{
    delete_track_by_link_id, get_single_stream_track_at_time_offset,
    get_single_stream_track_by_link_id, get_single_stream_track_by_order_id, get_stream_tracks,
    remove_tracks_by_track_id, GetUserStreamTracksParams, TrackFileLinkMergedRow,
};
use crate::storage::db::repositories::{StreamRow, StreamStatus};
use crate::system::now;
use crate::MySqlClient;
use chrono::Duration;
use futures::SinkExt;
use reqwest::StatusCode;
use std::future::Future;
use std::ops::{DerefMut, Index, Neg};
use std::pin::Pin;
use tracing::log::kv::Source;
use tracing::{debug, error};

#[derive(thiserror::Error, Debug)]
pub(crate) enum StreamServiceError {
    #[error("No permission to access this stream")]
    Forbidden,
    #[error("Stream does not exist")]
    StreamNotFound,
    #[error("Stream has unexpected state")]
    UnexpectedState,
    #[error("Repository error: {0}")]
    RepositoryError(#[from] RepositoryError),
    #[error("Database error: {0}")]
    DatabaseError(#[from] sqlx::Error),
    #[error("Track index out of bounds")]
    TrackIndexOutOfBounds,
}

#[derive(Clone)]
pub(crate) struct StreamServiceFactory {
    mysql_client: MySqlClient,
    radio_streamer_config: RadioStreamerConfig,
}

impl StreamServiceFactory {
    pub(crate) fn create(
        mysql_client: &MySqlClient,
        radio_streamer_config: &RadioStreamerConfig,
    ) -> Self {
        Self {
            mysql_client: mysql_client.clone(),
            radio_streamer_config: radio_streamer_config.clone(),
        }
    }

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
            None => return Err(StreamServiceError::StreamNotFound),
        };

        if &stream_row.uid != user_id {
            return Err(StreamServiceError::Forbidden);
        }

        Ok(StreamService::create(
            stream_id.clone(),
            self.mysql_client.clone(),
            self.radio_streamer_config.clone(),
        ))
    }
}

pub(crate) struct StreamService {
    stream_id: StreamId,
    mysql_client: MySqlClient,
    radio_streamer_config: RadioStreamerConfig,
}

impl StreamService {
    pub(crate) fn create(
        stream_id: StreamId,
        mysql_client: MySqlClient,
        radio_streamer_config: RadioStreamerConfig,
    ) -> Self {
        Self {
            stream_id,
            mysql_client,
            radio_streamer_config,
        }
    }

    pub(crate) async fn play(&self) -> Result<(), StreamServiceError> {
        let position = Duration::zero();

        let mut connection = self.mysql_client.connection().await?;
        self.play_internal(&mut connection, &position).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn stop(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.stop_internal(&mut connection).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn seek_forward(&self, time: Duration) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        seek_user_stream_forward(&mut connection, &self.stream_id, &time).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn seek_backward(&self, time: Duration) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        seek_user_stream_forward(&mut connection, &self.stream_id, &-time).await?;
        drop(connection);

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn play_next(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        let (track, position) = match self.get_now_playing(&mut connection).await? {
            Some(now_playing) => now_playing,
            None => return Err(StreamServiceError::StreamNotFound),
        };

        let next_time_offset = Duration::milliseconds(
            match get_single_stream_track_by_order_id(
                &mut connection,
                &self.stream_id,
                &(track.link.t_order + 1),
            )
            .await?
            {
                Some(track) => track.link.time_offset,
                None => 0,
            },
        );

        self.play_internal(&mut connection, &next_time_offset)
            .await?;

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn play_prev(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        let (track, position) = match self.get_now_playing(&mut connection).await? {
            Some(now_playing) => now_playing,
            None => return Err(StreamServiceError::StreamNotFound),
        };

        let next_time_offset = Duration::milliseconds(
            match get_single_stream_track_by_order_id(
                &mut connection,
                &self.stream_id,
                &(track.link.t_order - 1),
            )
            .await?
            {
                Some(track) => track.link.time_offset,
                None => 0,
            },
        );

        self.play_internal(&mut connection, &next_time_offset)
            .await?;

        self.notify_streams();

        Ok(())
    }

    pub(crate) async fn play_by_order_id(
        &self,
        order_id: &OrderId,
    ) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        match get_single_stream_track_by_order_id(&mut connection, &self.stream_id, order_id)
            .await?
        {
            Some(track) => {
                let position = Duration::milliseconds(track.link.time_offset);
                self.play_internal(&mut connection, &position).await?;

                self.notify_streams();

                Ok(())
            }
            None => return Err(StreamServiceError::TrackIndexOutOfBounds),
        }
    }

    pub(crate) async fn remove_track_by_link_id(
        &self,
        link_id: &LinkId,
    ) -> Result<(), StreamServiceError> {
        let link_id = link_id.clone();
        let stream_id = self.stream_id.clone();

        self.update_stream_in_transaction(|connection| {
            Box::pin(async move {
                delete_track_by_link_id(connection, &link_id, &stream_id).await?;
                Ok(())
            })
        })
        .await
    }

    pub(crate) async fn remove_tracks_by_track_id(
        &self,
        track_id: &TrackId,
    ) -> Result<(), StreamServiceError> {
        let track_id = track_id.clone();
        let stream_id = self.stream_id.clone();

        self.update_stream_in_transaction(|connection| {
            Box::pin(async move {
                remove_tracks_by_track_id(connection, &track_id, &stream_id).await?;
                Ok(())
            })
        })
        .await
    }

    async fn play_internal(
        &self,
        mut connection: &mut MySqlConnection,
        position: &Duration,
    ) -> Result<(), StreamServiceError> {
        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Playing,
            &Some(now()),
            &Some(position.num_milliseconds()),
        )
        .await?;

        Ok(())
    }

    async fn stop_internal(
        &self,
        mut connection: &mut MySqlConnection,
    ) -> Result<(), StreamServiceError> {
        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Stopped,
            &None,
            &None,
        )
        .await?;

        Ok(())
    }

    async fn get_now_playing(
        &self,
        mut connection: &mut MySqlConnection,
    ) -> Result<Option<(TrackFileLinkMergedRow, Duration)>, StreamServiceError> {
        let stream_row = match get_single_stream_by_id(&mut connection, &self.stream_id).await? {
            Some(stream_row) => stream_row,
            None => return Err(StreamServiceError::StreamNotFound),
        };

        match (
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

                Ok(get_single_stream_track_at_time_offset(
                    &mut connection,
                    &self.stream_id,
                    &Duration::milliseconds(playlist_time_position),
                )
                .await?)
            }
            _ => Ok(None),
        }
    }

    async fn update_stream_in_transaction<H>(&self, handler: H) -> Result<(), StreamServiceError>
    where
        H: for<'a> FnOnce(
            &'a mut MySqlConnection,
        ) -> Pin<
            Box<dyn Future<Output = Result<(), StreamServiceError>> + Send + 'a>,
        >,
    {
        let mut connection = self.mysql_client.transaction().await?;

        let now_playing = self
            .get_now_playing(&mut connection)
            .await
            .map(|option| option.map(|(entry, _)| entry))?;

        debug!("Now playing track before transaction: {:?}", &now_playing);

        handler(&mut connection).await?;

        if let Some(now_playing_entry) = now_playing {
            let new_track_offset = get_single_stream_track_by_link_id(
                &mut connection,
                &self.stream_id,
                &now_playing_entry.link.id,
            )
            .await
            .map(|row| row.map(|entry| entry.link.time_offset))?;

            match new_track_offset {
                Some(new_track_offset) => {
                    let offset_change = now_playing_entry.link.time_offset - new_track_offset;

                    debug!(
                        "Now playing track time_offset changed. Seeking forward seamlessly: {}",
                        offset_change
                    );

                    seek_user_stream_forward(
                        &mut connection,
                        &self.stream_id,
                        &Duration::milliseconds(offset_change),
                    )
                    .await?;
                }
                None => {
                    debug!("Now playing track has been removed during transaction. Moving forward to play the next track");

                    let next_track_time_offset = get_single_stream_track_by_order_id(
                        &mut connection,
                        &self.stream_id,
                        &now_playing_entry.link.t_order,
                    )
                    .await
                    .map(|row| row.map(|entry| entry.link.time_offset))?;

                    match next_track_time_offset {
                        Some(next_track_time_offset) => {
                            debug!(
                                "Going to restart stream with initial time_offset: {}",
                                next_track_time_offset
                            );

                            update_stream_status(
                                &mut connection,
                                &self.stream_id,
                                &StreamStatus::Playing,
                                &Some(now()),
                                &Some(next_track_time_offset),
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

    fn notify_streams(&self) {
        let endpoint = self.radio_streamer_config.endpoint.clone();
        let token = self.radio_streamer_config.token.clone();
        let stream_id = self.stream_id.clone();

        actix_rt::spawn(async move {
            let client = reqwest::Client::new();
            let response = match client
                .get(format!("{}/restart/{}", endpoint, *stream_id))
                .header("token", token)
                .send()
                .await
            {
                Ok(res) => res,
                Err(error) => {
                    error!(
                        ?error,
                        "Error occurred on sending request to radio streamer"
                    );
                    return;
                }
            };

            let status = response.status();

            if !matches!(status, StatusCode::OK) {
                let body = response.text().await;

                error!(?status, ?body, "Unexpected response")
            }
        });
    }
}
