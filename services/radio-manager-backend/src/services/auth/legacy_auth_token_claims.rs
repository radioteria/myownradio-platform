use crate::data_structures::UserId;
use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct LegacyAuthTokenData {
    #[serde(rename = "TOKEN")]
    pub(crate) token: String,
}

#[derive(Deserialize, Serialize, Debug, Clone)]
pub(crate) struct LegacyAuthTokenClaims {
    pub(crate) id: String,
    pub(crate) data: LegacyAuthTokenData,
}
