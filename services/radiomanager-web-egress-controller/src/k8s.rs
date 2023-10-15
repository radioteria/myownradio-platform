pub(crate) struct K8sClient {
    client: kube::Client,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum K8sClientError {
    #[error(transparent)]
    KubeClient(#[from] kube::Error),
}

impl K8sClient {
    pub(crate) async fn create() -> Result<Self, K8sClientError> {
        let client = kube::Client::try_default().await?;

        Ok(Self { client })
    }
}
