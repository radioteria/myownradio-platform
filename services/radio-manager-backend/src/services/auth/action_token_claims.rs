use crate::data_structures::UserId;
use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) enum Action {
    ResetPassword,
}

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct ActionTokenClaims {
    pub(crate) exp: usize,
    pub(crate) user_id: UserId,
    pub(crate) actions: Vec<Action>,
}
