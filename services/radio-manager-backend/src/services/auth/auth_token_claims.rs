use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct AuthTokenClaim {
    pub(crate) methods: Vec<String>,
    pub(crate) paths: Vec<String>,
}

pub(crate) type AuthTokenClaims = Vec<AuthTokenClaim>;
