use serde::Deserialize;

#[derive(Deserialize)]
pub(crate) struct Config {
    pub(crate) bind_address: String,
    pub(crate) egress_image_name: String,
    pub(crate) egress_image_tag: String,
}

impl Config {
    pub(crate) fn from_env() -> Self {
        envy::from_env().expect("Unable to parse environment variables")
    }
}
