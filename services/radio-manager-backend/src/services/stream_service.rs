use crate::config::RadioStreamerConfig;
use crate::data_structures::{LinkId, OrderId, StreamId, TrackId, UserId};
use crate::mysql_client::MySqlConnection;
use crate::pubsub_client::{PubsubClient, PubsubClientError};
use crate::services::stream_service_utils::get_now_playing;
use crate::storage::db::repositories::errors::RepositoryError;
use crate::storage::db::repositories::streams::{
    get_single_stream_by_id, seek_user_stream_forward, update_stream_status,
};
use crate::storage::db::repositories::user_stream_tracks::{
    delete_track_by_link_id, get_single_stream_track_by_link_id,
    get_single_stream_track_by_order_id, remove_tracks_by_track_id,
};
use crate::storage::db::repositories::StreamStatus;
use crate::system::now;
use crate::MySqlClient;
use chrono::Duration;
use reqwest::StatusCode;
use std::future::Future;
use std::pin::Pin;
use std::time::{SystemTime, UNIX_EPOCH};
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
    #[error("Pubsub client error: {0}")]
    PubsubClientError(#[from] PubsubClientError),
}

#[derive(Clone)]
pub(crate) struct StreamServiceFactory {
    mysql_client: MySqlClient,
    radio_streamer_config: RadioStreamerConfig,
    pubsub_client: PubsubClient,
}

impl StreamServiceFactory {
    pub(crate) fn create(
        mysql_client: &MySqlClient,
        radio_streamer_config: &RadioStreamerConfig,
        pubsub_client: &PubsubClient,
    ) -> Self {
        Self {
            mysql_client: mysql_client.clone(),
            radio_streamer_config: radio_streamer_config.clone(),
            pubsub_client: pubsub_client.clone(),
        }
    }

    pub(crate) async fn create_service(
        &self,
        stream_id: &StreamId,
    ) -> Result<StreamService, StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        let stream_row = match get_single_stream_by_id(&mut connection, stream_id).await? {
            Some(row) => row,
            None => {
                return Err(StreamServiceError::StreamNotFound);
            }
        };

        drop(connection);

        Ok(StreamService::create(
            stream_id.clone(),
            stream_row.uid.clone(),
            self.mysql_client.clone(),
            self.radio_streamer_config.clone(),
            self.pubsub_client.clone(),
        ))
    }

    pub(crate) async fn create_service_for_user(
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
            user_id.clone(),
            self.mysql_client.clone(),
            self.radio_streamer_config.clone(),
            self.pubsub_client.clone(),
        ))
    }
}

pub(crate) struct StreamService {
    stream_id: StreamId,
    user_id: UserId,
    mysql_client: MySqlClient,
    radio_streamer_config: RadioStreamerConfig,
    pubsub_client: PubsubClient,
}

impl StreamService {
    pub(crate) fn create(
        stream_id: StreamId,
        user_id: UserId,
        mysql_client: MySqlClient,
        radio_streamer_config: RadioStreamerConfig,
        pubsub_client: PubsubClient,
    ) -> Self {
        Self {
            user_id,
            stream_id,
            mysql_client,
            radio_streamer_config,
            pubsub_client,
        }
    }

    pub(crate) async fn play(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.play_internal(&mut connection).await?;
        drop(connection);

        self.notify_streams();

        self.pubsub_client
            .restart_channel(&self.stream_id, &self.user_id)
            .await?;

        Ok(())
    }

    pub(crate) async fn pause(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.pause_internal(&mut connection).await?;
        drop(connection);

        self.notify_streams();

        self.pubsub_client
            .restart_channel(&self.stream_id, &self.user_id)
            .await?;

        Ok(())
    }

    pub(crate) async fn stop(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;
        self.stop_internal(&mut connection).await?;
        drop(connection);

        self.notify_streams();

        self.pubsub_client
            .restart_channel(&self.stream_id, &self.user_id)
            .await?;

        Ok(())
    }

    pub(crate) async fn play_next(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        let (track, status) =
            match get_now_playing(&SystemTime::now(), &self.stream_id, &mut connection).await? {
                Some((track, _, _, status)) => (track, status),
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

        match status {
            StreamStatus::Playing => {
                self.play_from_position_internal(&mut connection, &next_time_offset)
                    .await?;
            }
            StreamStatus::Paused => {
                self.pause_at_position_internal(&mut connection, &next_time_offset)
                    .await?;
            }
            _ => {}
        }
        drop(connection);

        self.notify_streams();

        self.pubsub_client
            .restart_channel(&self.stream_id, &self.user_id)
            .await?;

        Ok(())
    }

    pub(crate) async fn play_prev(&self) -> Result<(), StreamServiceError> {
        let mut connection = self.mysql_client.connection().await?;

        let (track, status) =
            match get_now_playing(&SystemTime::now(), &self.stream_id, &mut connection).await? {
                Some((track, _, _, status)) => (track, status),
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

        match status {
            StreamStatus::Playing => {
                self.play_from_position_internal(&mut connection, &next_time_offset)
                    .await?;
            }
            StreamStatus::Paused => {
                self.pause_at_position_internal(&mut connection, &next_time_offset)
                    .await?;
            }
            _ => {}
        }
        drop(connection);

        self.notify_streams();

        self.pubsub_client
            .restart_channel(&self.stream_id, &self.user_id)
            .await?;

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
                self.play_from_position_internal(&mut connection, &position)
                    .await?;
                drop(connection);

                self.notify_streams();

                self.pubsub_client
                    .restart_channel(&self.stream_id, &self.user_id)
                    .await?;

                Ok(())
            }
            None => return Err(StreamServiceError::TrackIndexOutOfBounds),
        }
    }

    #[allow(dead_code)]
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

    #[allow(dead_code)]
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

    async fn play_from_position_internal(
        &self,
        mut connection: &mut MySqlConnection,
        position: &Duration,
    ) -> Result<(), StreamServiceError> {
        let started_at = SystemTime::now()
            .duration_since(UNIX_EPOCH)
            .unwrap()
            .as_millis() as i64;

        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Playing,
            &Some(started_at),
            &Some(position.num_milliseconds()),
        )
        .await?;

        Ok(())
    }

    async fn pause_at_position_internal(
        &self,
        mut connection: &mut MySqlConnection,
        position: &Duration,
    ) -> Result<(), StreamServiceError> {
        let started_at = SystemTime::now()
            .duration_since(UNIX_EPOCH)
            .unwrap()
            .as_millis() as i64;

        update_stream_status(
            &mut connection,
            &self.stream_id,
            &StreamStatus::Paused,
            &Some(started_at),
            &Some(position.num_milliseconds()),
        )
        .await?;

        Ok(())
    }

    async fn play_internal(
        &self,
        mut connection: &mut MySqlConnection,
    ) -> Result<(), StreamServiceError> {
        let now = SystemTime::now();

        match get_now_playing(&now, &self.stream_id, &mut connection).await? {
            // If it was stopped, then play from the beginning.
            None | Some((_, _, _, StreamStatus::Stopped)) => {
                let started_at = now.duration_since(UNIX_EPOCH).unwrap().as_millis() as i64;

                update_stream_status(
                    &mut connection,
                    &self.stream_id,
                    &StreamStatus::Playing,
                    &Some(started_at),
                    &Some(0),
                )
                .await?;
            }
            // If it was on pause, then play.
            Some((curr, _, position, StreamStatus::Paused)) => {
                let started_at = now.duration_since(UNIX_EPOCH).unwrap().as_millis() as i64;

                update_stream_status(
                    &mut connection,
                    &self.stream_id,
                    &StreamStatus::Playing,
                    &Some(started_at),
                    &Some(curr.link.time_offset + position.num_milliseconds()),
                )
                .await?;
            }
            // If it was already playing, do nothing.
            Some((_, _, _, StreamStatus::Playing)) => {}
        }

        Ok(())
    }

    async fn pause_internal(
        &self,
        mut connection: &mut MySqlConnection,
    ) -> Result<(), StreamServiceError> {
        let now = SystemTime::now();

        match get_now_playing(&now, &self.stream_id, &mut connection).await? {
            // If it was stopped, then pause at the beginning.
            None | Some((_, _, _, StreamStatus::Stopped)) => {
                let started_at = now.duration_since(UNIX_EPOCH).unwrap().as_millis() as i64;

                update_stream_status(
                    &mut connection,
                    &self.stream_id,
                    &StreamStatus::Paused,
                    &Some(started_at),
                    &Some(0),
                )
                .await?;
            }
            // If it was playing, then pause.
            Some((curr, _, position, StreamStatus::Playing)) => {
                let started_at = now.duration_since(UNIX_EPOCH).unwrap().as_millis() as i64;

                update_stream_status(
                    &mut connection,
                    &self.stream_id,
                    &StreamStatus::Paused,
                    &Some(started_at),
                    &Some(curr.link.time_offset + position.num_milliseconds()),
                )
                .await?;
            }
            // If it was already on pause, do nothing.
            Some((_, _, _, StreamStatus::Paused)) => {}
        }

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

    async fn update_stream_in_transaction<H>(&self, handler: H) -> Result<(), StreamServiceError>
    where
        H: for<'a> FnOnce(
            &'a mut MySqlConnection,
        ) -> Pin<
            Box<dyn Future<Output = Result<(), StreamServiceError>> + Send + 'a>,
        >,
    {
        let mut connection = self.mysql_client.transaction().await?;

        let now_playing = get_now_playing(&SystemTime::now(), &self.stream_id, &mut connection)
            .await
            .map(|option| option.map(|(entry, _, _, _)| entry))?;

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

                    self.pubsub_client
                        .restart_channel(&self.stream_id, &self.user_id)
                        .await?;
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
