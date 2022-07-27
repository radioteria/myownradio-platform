use std::ops::Deref;

#[derive(Serialize, Clone)]
pub(crate) struct UserId(usize);

impl Deref for UserId {
    type Target = usize;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Clone)]
pub(crate) struct TrackId(usize);

impl Deref for TrackId {
    type Target = usize;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(Serialize, Clone)]
pub(crate) struct FileId(usize);

impl Deref for FileId {
    type Target = usize;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}
