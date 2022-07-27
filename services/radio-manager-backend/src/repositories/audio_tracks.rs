use crate::models::audio_track::AudioTrack;
use crate::models::types::UserId;
use crate::mysql_client::MySqlClient;
use serde_repr::{Deserialize_repr, Serialize_repr};
use sqlx::{query_as, Error};
use std::ops::Deref;

#[derive(Serialize_repr, Deserialize_repr)]
#[repr(u8)]
pub(crate) enum SortingColumn {
    TrackId,
    Title,
    Artist,
    Genre,
    Duration,
}

impl SortingColumn {
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingColumn::TrackId => "tid",
            SortingColumn::Title => "title",
            SortingColumn::Artist => "artist",
            SortingColumn::Genre => "genre",
            SortingColumn::Duration => "duration",
        }
    }
}

#[derive(Serialize_repr, Deserialize_repr)]
#[repr(u8)]
pub(crate) enum SortingOrder {
    Desc,
    Asc,
}

impl SortingOrder {
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingOrder::Desc => "DESC",
            SortingOrder::Asc => "ASC",
        }
    }
}

#[derive(Clone)]
pub(crate) struct AudioTracksRepository {
    mysql_client: MySqlClient,
}

impl AudioTracksRepository {
    pub(crate) fn new(mysql_client: &MySqlClient) -> Self {
        Self {
            mysql_client: mysql_client.clone(),
        }
    }

    pub async fn get_user_audio_tracks(
        &self,
        user_id: &UserId,
        color: &u32,
        filter: &Option<String>,
        offset: &u32,
        sorting_column: &SortingColumn,
        sorting_order: &SortingOrder,
    ) -> Result<Vec<Caption>, Error> {
        let audio_tracks =
            query_as::<_, AudioTrack>("SELECT * FROM r_tracks WHERE uid = $1 ORDER BY $2 $3")
                .bind(user_id.deref())
                .bind(sorting_column.as_str())
                .bind(sorting_order.as_str())
                .fetch_all(self.mysql_client.connection())
                .await
                .map(|rows| {
                    rows.into_iter()
                        .map(Into::into)
                        .collect::<Vec<AudioTrack>>()
                })?;

        Ok(audio_tracks)
    }
}
