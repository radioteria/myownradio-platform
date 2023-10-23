use super::auth_token_claims::AuthTokenClaims;
use tracing::debug;

pub(crate) trait IsActionAllowed {
    fn is_action_allowed(&self, method: &str, path: &str) -> bool;
}

impl IsActionAllowed for AuthTokenClaims {
    fn is_action_allowed(&self, method: &str, uri: &str) -> bool {
        let method_as_string = method.to_string();
        let is_allowed = self.claims.iter().any(|claim| {
            claim.methods.contains(&method_as_string)
                && claim
                    .uris
                    .iter()
                    .any(|claim_uri| uri.starts_with(claim_uri))
        });

        if !is_allowed {
            debug!(
                "Action not allowed by any of claims: method={} uri={} claims={:?}",
                method, uri, self.claims
            );
        }

        is_allowed
    }
}
