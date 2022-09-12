use serde::{Deserialize, Serialize};
use std::ops::Deref;

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug, Eq, PartialEq)]
#[sqlx(transparent)]
pub(crate) struct UserId(i32);

impl Deref for UserId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl From<i32> for UserId {
    fn from(id: i32) -> Self {
        UserId(id)
    }
}

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug)]
#[sqlx(transparent)]
pub(crate) struct TrackId(i32);

impl Deref for TrackId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug)]
#[sqlx(transparent)]
pub(crate) struct FileId(i32);

impl Deref for FileId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug)]
#[sqlx(transparent)]
pub(crate) struct StreamId(i32);

impl Deref for StreamId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}
