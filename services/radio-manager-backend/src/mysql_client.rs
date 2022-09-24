use crate::config::MySqlConfig;
use std::ops::{Deref, DerefMut};

use sqlx::pool::PoolConnection;
use sqlx::{mysql, Error, MySql, Pool, Transaction};

pub(crate) enum MySqlConnection {
    Pool(PoolConnection<MySql>),
    Transaction(Transaction<'static, MySql>),
}

impl Deref for MySqlConnection {
    type Target = sqlx::MySqlConnection;

    fn deref(&self) -> &Self::Target {
        match self {
            MySqlConnection::Transaction(tx) => tx.deref(),
            MySqlConnection::Pool(pool) => pool.deref(),
        }
    }
}

impl MySqlConnection {
    pub(crate) async fn commit(self) -> Result<(), Error> {
        if let MySqlConnection::Transaction(tx) = self {
            tx.commit().await?;
        }

        Ok(())
    }
}

impl DerefMut for MySqlConnection {
    fn deref_mut(&mut self) -> &mut Self::Target {
        match self {
            MySqlConnection::Transaction(tx) => tx.deref_mut(),
            MySqlConnection::Pool(pool) => pool.deref_mut(),
        }
    }
}

#[derive(Clone)]
pub(crate) struct MySqlClient {
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
            .fetch_one(self.connection().await?.deref_mut())
            .await?;

        Ok(())
    }

    pub(crate) async fn connection(&self) -> Result<MySqlConnection, Error> {
        Ok(MySqlConnection::Pool(self.pool.acquire().await?))
    }

    pub(crate) async fn transaction(&self) -> Result<MySqlConnection, Error> {
        Ok(MySqlConnection::Transaction(self.pool.begin().await?))
    }
}
