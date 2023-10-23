use crate::data_structures::UserId;
use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct AuthTokenClaim {
    pub(crate) methods: Vec<&'static str>,
    pub(crate) uris: Vec<&'static str>,
}

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct AuthTokenClaims {
    pub(crate) exp: usize,
    pub(crate) user_id: UserId,
    pub(crate) claims: Vec<AuthTokenClaim>,
}
