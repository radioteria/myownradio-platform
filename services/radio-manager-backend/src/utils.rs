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
