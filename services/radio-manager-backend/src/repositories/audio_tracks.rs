use crate::models::audio_track::AudioTrack;
use crate::models::types::UserId;
use crate::mysql_client::MySqlClient;
use serde_repr::{Deserialize_repr, Serialize_repr};
use slog::{trace, Logger};
use sqlx::{query, query_as, Database, Error, Execute, QueryBuilder, Type};
use std::ops::Deref;

// Copied from Defaults.php
const DEFAULT_TRACKS_PER_REQUEST: i32 = 50;

#[derive(Serialize_repr, Deserialize_repr)]
#[repr(u8)]
pub(crate) enum SortingColumn {
    TrackId,
    Title,
    Artist,
    Genre,
    Duration,
}

impl Default for SortingColumn {
    fn default() -> Self {
        Self::TrackId
    }
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

impl Default for SortingOrder {
    fn default() -> Self {
        Self::Desc
    }
}

impl SortingOrder {
    fn as_str(&self) -> &str {
        match self {
            SortingOrder::Desc => "DESC",
            SortingOrder::Asc => "ASC",
        }
    }
}

#[derive(Clone)]
pub(crate) struct AudioTracksRepository {
    mysql_client: MySqlClient,
    logger: Logger,
}

impl AudioTracksRepository {
    pub(crate) fn new(mysql_client: &MySqlClient, logger: &Logger) -> Self {
        Self {
            mysql_client: mysql_client.clone(),
            logger: logger.clone(),
        }
    }

    pub async fn get_user_audio_tracks(
        &self,
        user_id: &UserId,
        color: &Option<u32>,
        filter: &Option<String>,
        offset: &u32,
        unused: &bool,
        sorting_column: &SortingColumn,
        sorting_order: &SortingOrder,
    ) -> Result<Vec<AudioTrack>, Error> {
        let mut builder = QueryBuilder::new("SELECT * FROM r_tracks");

        builder.push(" WHERE uid = ");
        builder.push_bind(user_id.deref());

        if let Some(filter) = filter {
            builder.push(" AND MATCH(artist, title, genre) AGAINST (");
            builder.push_bind(filter);
            builder.push(" IN BOOLEAN MODE)");
        };

        if let Some(color) = color {
            builder.push(" AND color = ");
            builder.push_bind(color);
        };

        if unused {
            builder.push(" AND used_count = 0");
        }

        builder.push(format_args!(
            " ORDER BY {} {}",
            sorting_column.as_str(),
            sorting_order.as_str()
        ));

        builder.push(" LIMIT ");
        builder.push_bind(offset);
        builder.push(", ");
        builder.push_bind(DEFAULT_TRACKS_PER_REQUEST);

        let query = builder.build();

        trace!(self.logger, "Running SQL query: {}", query.sql());

        let audio_tracks = query
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
