use super::auth_token_claims::AuthTokenClaims;

pub(crate) trait IsActionAllowed {
    fn is_action_allowed(&self, method: &str, path: &str) -> bool;
}

impl IsActionAllowed for AuthTokenClaims {
    fn is_action_allowed(&self, method: &str, uri: &str) -> bool {
        let method = method.to_string();
        let uri = uri.to_string();

        self.claims
            .iter()
            .any(|claim| claim.methods.contains(&method) && claim.paths.contains(&uri))
    }
}
