use super::auth_token_claims::AuthTokenClaims;
use crate::services::auth::action_token_claims::ActionTokenClaims;
use crate::services::auth::legacy_auth_token_claims::LegacyAuthTokenClaims;
use jsonwebtoken::{decode, encode, Algorithm, DecodingKey, EncodingKey, Header, Validation};
use std::collections::HashSet;
use std::time::Duration;
use tracing::warn;

const TOKEN_EXPIRES_AFTER: Duration = Duration::from_secs(3600);

#[derive(Clone)]
pub(crate) struct AuthTokenService {
    secret_key: String,
    legacy_secret_key: String,
}

impl AuthTokenService {
    pub(crate) fn create(secret_key: &str, legacy_secret_key: &str) -> Self {
        Self {
            secret_key: secret_key.to_string(),
            legacy_secret_key: legacy_secret_key.to_string(),
        }
    }

    pub(crate) fn sign_claims(&self, claims: AuthTokenClaims) -> String {
        let key = EncodingKey::from_secret(self.secret_key.as_ref());
        let header = Header::new(Algorithm::HS256);

        encode(&header, &claims, &key).expect("Unable to sign claims")
    }

    pub(crate) fn verify_claims(&self, token: &str) -> Option<AuthTokenClaims> {
        let key = DecodingKey::from_secret(self.secret_key.as_ref());

        match decode::<AuthTokenClaims>(token, &key, &Validation::default()) {
            Ok(data) => Some(data.claims.clone()),
            Err(error) => {
                warn!("Unable to verify claims: {}", error);
                None
            }
        }
    }

    pub(crate) fn verify_legacy_claims(&self, token: &str) -> Option<LegacyAuthTokenClaims> {
        let key = DecodingKey::from_secret(self.legacy_secret_key.as_ref());
        let mut validation = Validation::default();

        validation.validate_exp = false;
        validation.required_spec_claims = HashSet::new();

        match decode::<LegacyAuthTokenClaims>(token, &key, &validation) {
            Ok(data) => Some(data.claims.clone()),
            Err(error) => {
                warn!("Unable to verify claims: {}", error);
                None
            }
        }
    }

    pub(crate) fn sign_legacy_claims(&self, claims: LegacyAuthTokenClaims) -> String {
        let key = EncodingKey::from_secret(self.legacy_secret_key.as_ref());
        let header = Header::new(Algorithm::HS256);

        encode(&header, &claims, &key).expect("Unable to sign legacy claims")
    }

    pub(crate) fn sign_action_claims(&self, claims: ActionTokenClaims) -> String {
        let key = EncodingKey::from_secret(self.secret_key.as_ref());
        let header = Header::new(Algorithm::HS256);

        encode(&header, &claims, &key).expect("Unable to sign claims")
    }

    pub(crate) fn verify_action_claims(&self, token: &str) -> Option<ActionTokenClaims> {
        let key = DecodingKey::from_secret(self.secret_key.as_ref());

        match decode::<ActionTokenClaims>(token, &key, &Validation::default()) {
            Ok(data) => Some(data.claims.clone()),
            Err(error) => {
                warn!("Unable to verify claims: {}", error);
                None
            }
        }
    }
}
