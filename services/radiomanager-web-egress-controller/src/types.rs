use serde::Deserialize;
use std::ops::Deref;

#[derive(Clone, Debug, Deserialize)]
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
