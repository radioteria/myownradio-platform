use serde::Serialize;
use std::ops::Deref;

#[derive(Serialize, Clone, sqlx::Type)]
#[sqlx(transparent)]
pub(crate) struct UserId(i32);

impl Deref for UserId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Clone, sqlx::Type)]
#[sqlx(transparent)]
pub(crate) struct TrackId(i32);

impl Deref for TrackId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Clone, sqlx::Type)]
#[sqlx(transparent)]
pub(crate) struct FileId(i32);

impl Deref for FileId {
    type Target = i32;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}
