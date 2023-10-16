use super::auth_token_claims::AuthTokenClaims;
use jsonwebtoken::{decode, encode, Algorithm, DecodingKey, EncodingKey, Header, Validation};
use std::time::Duration;

const TOKEN_EXPIRES_AFTER: Duration = Duration::from_secs(3600);

#[derive(Clone)]
pub(crate) struct AuthTokenService {
    secret_key: String,
}

impl AuthTokenService {
    pub(crate) fn create(secret_key: &str) -> Self {
        Self {
            secret_key: secret_key.to_string(),
        }
    }

    pub(crate) fn sign_claims(&self, claims: AuthTokenClaims) -> String {
        let key = EncodingKey::from_secret(self.secret_key.as_ref());
        let header = Header::new(Algorithm::HS256);

        encode(&header, &claims, &key).expect("Unable to sign claims")
    }

    pub(crate) fn verify_claims(&self, token: &str) -> Option<AuthTokenClaims> {
        let key = DecodingKey::from_secret(self.secret_key.as_ref());
        let token_data = decode::<AuthTokenClaims>(token, &key, &Validation::default()).ok()?;

        Some(token_data.claims.clone())
    }
}
