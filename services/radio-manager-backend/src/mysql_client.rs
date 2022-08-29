use crate::config::MySqlConfig;

use sqlx::{mysql, Error, MySql, Pool, Transaction};

#[derive(Clone)]
pub struct MySqlClient {
    pool: Pool<MySql>,
}

impl MySqlClient {
    pub(crate) async fn new(config: &MySqlConfig) -> Result<Self, Error> {
        let pool = mysql::MySqlPoolOptions::new()
            .max_connections(10)
            .connect(&config.connection_string())
            .await?;

        Ok(Self { pool })
    }

    pub(crate) async fn check_connection(&self) -> Result<(), Error> {
        let _ = sqlx::query("SELECT NOW()")
            .fetch_one(self.connection())
            .await?;

        Ok(())
    }

    pub(crate) fn connection(&self) -> &Pool<MySql> {
        &self.pool
    }

    pub(crate) async fn transaction(&self) -> Result<Transaction<MySql>, Error> {
        self.connection().begin().await
    }
}
