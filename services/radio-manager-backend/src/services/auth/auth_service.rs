use crate::data_structures::UserId;
use crate::mysql_client::MySqlClient;
use crate::services::auth::{AuthTokenService, LegacyAuthTokenClaims, LegacyAuthTokenData};
use crate::storage::db::repositories::errors::RepositoryError;
use crate::storage::db::repositories::{legacy_sessions, users};
use crate::utils::verify_password;
use serde::Serialize;
use std::ops::Deref;

#[derive(Serialize)]
pub(crate) struct LoggedInUser {
    id: UserId,
    email: String,
}

pub(crate) struct LegacyToken(String);

impl Deref for LegacyToken {
    type Target = String;

    fn deref(&self) -> &Self::Target {
        &self.0
    }
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum LoginError {
    #[error("Bad credentials")]
    BadCredentials,
    #[error(transparent)]
    DatabaseError(#[from] sqlx::Error),
    #[error(transparent)]
    RepositoryError(#[from] RepositoryError),
}

#[derive(Clone)]
pub(crate) struct AuthService {
    mysql_client: MySqlClient,
    token_service: AuthTokenService,
}

impl AuthService {
    pub(crate) fn new(mysql_client: MySqlClient, token_service: AuthTokenService) -> Self {
        Self {
            mysql_client,
            token_service,
        }
    }

    pub(crate) async fn legacy_login(
        &self,
        email: &str,
        password: &str,
    ) -> Result<(LoggedInUser, LegacyToken), LoginError> {
        let mut connection = self.mysql_client.connection().await?;

        let user = match users::get_user_by_email(&mut connection, email).await? {
            Some(user) => {
                let hashed_password = user.password.clone().unwrap_or_default();
                let is_valid =
                    verify_password(password, &hashed_password).expect("Unable to verify password");

                if !is_valid {
                    return Err(LoginError::BadCredentials);
                }

                user
            }
            None => {
                return Err(LoginError::BadCredentials);
            }
        };

        let legacy_session =
            legacy_sessions::create_legacy_session(&mut connection, &user.uid).await?;

        let token = self
            .token_service
            .sign_legacy_claims(LegacyAuthTokenClaims {
                id: legacy_session.session_id,
                data: LegacyAuthTokenData {
                    token: legacy_session.token,
                },
            });

        Ok((
            LoggedInUser {
                id: user.uid,
                email: user.mail,
            },
            LegacyToken(token),
        ))
    }
}
