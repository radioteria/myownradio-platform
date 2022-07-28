use crate::models::audio_track::AudioTrack;
use crate::models::types::UserId;
use crate::mysql_client::MySqlClient;
use serde_repr::{Deserialize_repr, Serialize_repr};
use sql_builder::bind::Bind;
use sql_builder::SqlBuilder;
use sqlx::{query_as, Error};
use std::ops::Deref;

// Copied from Defaults.php
const DEFAULT_TRACKS_PER_REQUEST: usize = 50;

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
    fn as_str(&self) -> &str {
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
    fn is_desc(&self) -> bool {
        match self {
            SortingOrder::Desc => true,
            SortingOrder::Asc => false,
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
        color: &Option<u32>,
        filter: &Option<String>,
        offset: &u32,
        sorting_column: &SortingColumn,
        sorting_order: &SortingOrder,
    ) -> Result<Vec<AudioTrack>, Error> {
        let builder = {
            let mut query = SqlBuilder::select_from("r_tracks")
                .field("*")
                .and_where_eq("uid", user_id.deref())
                .order_by(sorting_column.as_str(), sorting_order.is_desc())
                .to_owned();

            if let Some(filter) = filter {
                query.and_where(
                    "MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)".binds(&[filter]),
                );
            }

            if let Some(color) = color {
                query.and_where_eq("color", color);
            }

            query.offset(offset).limit(DEFAULT_TRACKS_PER_REQUEST);

            query
        };

        let audio_tracks =
            query_as::<_, AudioTrack>(&builder.sql().expect("Unable to generate SQL-expression"))
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
