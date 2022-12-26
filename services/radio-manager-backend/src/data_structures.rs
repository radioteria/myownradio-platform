use serde::{Deserialize, Serialize};
use serde_repr::{Deserialize_repr, Serialize_repr};
use std::ops::Deref;

#[derive(Serialize_repr, Deserialize_repr, Debug)]
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
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingColumn::TrackId => "`r_tracks`.`tid`",
            SortingColumn::Title => "`r_tracks`.`title`",
            SortingColumn::Artist => "`r_tracks`.`artist`",
            SortingColumn::Genre => "`r_tracks`.`genre`",
            SortingColumn::Duration => "`r_tracks`.`duration`",
        }
    }
}

#[derive(Serialize_repr, Deserialize_repr, Debug)]
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
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingOrder::Desc => "DESC",
            SortingOrder::Asc => "ASC",
        }
    }
}

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

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug, Eq, PartialEq)]
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

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug)]
#[sqlx(transparent)]
pub(crate) struct LinkId(i64);

impl Deref for LinkId {
    type Target = i64;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Deserialize, Clone, sqlx::Type, Debug)]
#[sqlx(transparent)]
pub(crate) struct OrderId(i32);

impl Deref for OrderId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

impl std::ops::Add<i32> for OrderId {
    type Output = OrderId;

    fn add(self, rhs: i32) -> OrderId {
        OrderId(self.0 + rhs)
    }
}

impl std::ops::Sub<i32> for OrderId {
    type Output = OrderId;

    fn sub(self, rhs: i32) -> OrderId {
        OrderId(self.0 - rhs)
    }
}

// Copied from Defaults.php
pub(crate) const DEFAULT_TRACKS_PER_REQUEST: i64 = 50;
