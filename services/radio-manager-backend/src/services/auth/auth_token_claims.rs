use crate::data_structures::UserId;
use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct AuthTokenClaim {
    pub(crate) methods: Vec<String>,
    pub(crate) paths: Vec<String>,
}

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct AuthTokenClaims {
    user_id: Option<UserId>,
    claims: Vec<AuthTokenClaim>,
}
