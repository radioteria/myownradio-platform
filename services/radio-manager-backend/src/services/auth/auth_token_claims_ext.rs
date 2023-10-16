use super::auth_token_claims::AuthTokenClaims;

trait IsAllowed {
    fn is_allowed(&self, method: String, path: String) -> bool;
}

impl IsAllowed for AuthTokenClaims {
    fn is_allowed(&self, method: String, path: String) -> bool {
        self.iter()
            .any(|claim| claim.methods.contains(&method) && claim.paths.contains(&path))
    }
}
