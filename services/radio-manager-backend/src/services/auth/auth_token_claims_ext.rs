use super::auth_token_claims::AuthTokenClaims;
use tracing::debug;

pub(crate) trait IsActionAllowed {
    fn is_action_allowed(&self, method: &str, path: &str) -> bool;
}

impl IsActionAllowed for AuthTokenClaims {
    fn is_action_allowed(&self, method: &str, uri: &str) -> bool {
        let method = method.to_string();
        let uri = uri.to_string();

        let is_allowed = self
            .claims
            .iter()
            .any(|claim| claim.methods.contains(&method) && claim.uris.contains(&uri));

        if !is_allowed {
            debug!(
                "Action not allowed by any of claims: method={} uri={} claims={:?}",
                method, uri, self.claims
            );
        }

        is_allowed
    }
}
