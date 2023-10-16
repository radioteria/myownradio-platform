use super::auth_token_claims::AuthTokenClaims;
use hmac::{Hmac, Mac};
use jwt::{AlgorithmType, Header, SignWithKey, Token, VerifyWithKey};
use sha2::Sha384;

#[derive(Clone)]
pub(crate) struct AuthTokenService {
    key: Hmac<Sha384>,
}

impl AuthTokenService {
    pub(crate) fn create(secret_key: String) -> Self {
        let key: Hmac<Sha384> =
            Hmac::new_from_slice(secret_key.as_bytes()).expect("Unable to encrypt JWT key");

        Self { key }
    }

    pub(crate) fn sign_claims(&self, claims: AuthTokenClaims) -> String {
        let header = Header {
            algorithm: AlgorithmType::Hs384,
            ..Default::default()
        };

        let token = Token::new(header, claims)
            .sign_with_key(&self.key)
            .expect("Unable to sign claims");

        token.as_str().to_string()
    }

    pub(crate) fn verify_claims(&self, token: String) -> Option<AuthTokenClaims> {
        let token: Token<_, AuthTokenClaims, _> =
            VerifyWithKey::verify_with_key(token, &self.key).ok()?;
        let claims = token.claims();

        Some(claims.clone())
    }
}
