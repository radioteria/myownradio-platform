#[macro_export]
macro_rules! unwrap_or_return {
    ($x:expr, $r:expr) => {{
        match $x {
            Ok(value) => value,
            Err(_) => return $r,
        }
    }};
    ($x:expr) => {
        unwrap_or_return!($x, ())
    };
}
