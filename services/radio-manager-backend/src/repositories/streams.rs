use crate::models::stream::Stream;
use crate::models::types::{StreamId, UserId};
use crate::MySqlClient;
use slog::{trace, Logger};
use sqlx::{Error, Execute, QueryBuilder};
use std::ops::Deref;

#[derive(Clone)]
pub(crate) struct StreamsRepository {
    mysql_client: MySqlClient,
    logger: Logger,
}

impl StreamsRepository {
    pub(crate) fn new(mysql_client: &MySqlClient, logger: &Logger) -> Self {
        Self {
            mysql_client: mysql_client.clone(),
            logger: logger.clone(),
        }
    }

    pub(crate) async fn get_public_stream(
        &self,
        stream_id: &StreamId,
    ) -> Result<Option<Stream>, Error> {
        let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

        builder.push(" WHERE sid = ");
        builder.push_bind(stream_id.deref());

        builder.push(" OR permalink = ");
        builder.push_bind(stream_id.deref());

        let query = builder.build();

        trace!(self.logger, "Running SQL query: {}", query.sql());

        let stream = query
            .fetch_optional(self.mysql_client.connection())
            .await
            .map(|row| row.map(|ref row| row.into()))?;

        Ok(stream)
    }

    pub async fn get_single_user_stream(
        &self,
        user_id: &UserId,
        stream_id: &StreamId,
    ) -> Result<Option<Stream>, Error> {
        let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

        builder.push(" WHERE uid = ");
        builder.push_bind(user_id.deref());

        builder.push(" AND sid = ");
        builder.push_bind(stream_id.deref());

        let query = builder.build();

        trace!(self.logger, "Running SQL query: {}", query.sql());

        let streams = query
            .fetch_optional(self.mysql_client.connection())
            .await
            .map(|row| row.map(|ref row| row.into()))?;

        Ok(streams)
    }

    pub async fn get_user_streams(&self, user_id: &UserId) -> Result<Vec<Stream>, Error> {
        let mut builder = QueryBuilder::new("SELECT * FROM r_streams");

        builder.push(" WHERE uid = ");
        builder.push_bind(user_id.deref());

        let query = builder.build();

        trace!(self.logger, "Running SQL query: {}", query.sql());

        let streams = query
            .fetch_all(self.mysql_client.connection())
            .await
            .map(|rows| rows.iter().map(Into::into).collect())?;

        Ok(streams)
    }
}
