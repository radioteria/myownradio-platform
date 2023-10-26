use serde::Deserialize;

#[derive(Deserialize)]
pub(crate) struct RadiomanagerBackendSettings {
    #[serde(rename = "radiomanager_backend_endpoint")]
    pub(crate) endpoint: String,
}

#[derive(Deserialize)]
pub(crate) struct Config {
    pub(crate) bind_address: String,
    pub(crate) egress_image_name: String,
    pub(crate) egress_image_tag: String,
    pub(crate) egress_namespace: String,
    #[serde(flatten)]
    pub(crate) radiomanager_backend: RadiomanagerBackendSettings,
}

impl Config {
    pub(crate) fn from_env() -> Self {
        envy::from_env().expect("Unable to parse environment variables")
    }
}
