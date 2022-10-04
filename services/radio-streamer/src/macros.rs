#[macro_export]
macro_rules! upgrade_weak {
    ($x:ident, $r:expr) => {{
        match $x.upgrade() {
            Some(o) => o,
            None => return $r,
        }
    }};
    ($x:ident) => {
        upgrade_weak!($x, ())
    };
}

#[macro_export]
macro_rules! unwrap_some {
    ($x:ident, $r:expr) => {{
        match $x {
            Some(o) => o,
            None => return $r,
        }
    }};
    ($x:ident) => {
        unwrap_some!($x, ())
    };
}
