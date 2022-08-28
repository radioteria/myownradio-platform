use crate::config::MySqlConfig;

use sqlx::{mysql, Error, MySql, Pool};

#[derive(Clone)]
pub struct MySqlClient {
    pool: Pool<MySql>,
}

impl MySqlClient {
    pub async fn new(config: &MySqlConfig) -> Result<Self, Error> {
        let pool = mysql::MySqlPoolOptions::new()
            .max_connections(10)
            .connect(&config.connection_string())
            .await?;

        Ok(Self { pool })
    }

    pub async fn check_connection(&self) -> Result<(), Error> {
        let _ = sqlx::query("SELECT NOW()").fetch_one(&self.pool).await?;

        Ok(())
    }

    pub fn connection(&self) -> &Pool<MySql> {
        &self.pool
    }
}
