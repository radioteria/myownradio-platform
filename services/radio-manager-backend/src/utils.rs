use bcrypt::{hash, verify, DEFAULT_COST};

pub(crate) trait TeeResultUtils<I, E> {
    fn tee_ok<CB>(self, cb: CB) -> Self
    where
        CB: FnOnce(&I) -> ();
    fn tee_err<CB>(self, cb: CB) -> Self
    where
        CB: FnOnce(&E) -> ();
}

impl<I, E> TeeResultUtils<I, E> for Result<I, E> {
    fn tee_ok<CB>(self, cb: CB) -> Self
    where
        CB: FnOnce(&I) -> (),
    {
        if let Ok(item) = &self {
            cb(item)
        }

        self
    }

    fn tee_err<CB>(self, cb: CB) -> Self
    where
        CB: FnOnce(&E) -> (),
    {
        if let Err(err) = &self {
            cb(err)
        }

        self
    }
}

pub(crate) fn positive_mod(number: i64, by: i64) -> i64 {
    let mut number = number;

    while number.is_negative() {
        number += by;
    }

    number % by
}

pub(crate) fn hash_password(password: &str) -> Result<String, bcrypt::BcryptError> {
    hash(password, DEFAULT_COST)
}

pub(crate) fn verify_password(password: &str, hash: &str) -> Result<bool, bcrypt::BcryptError> {
    verify(password, hash)
}
