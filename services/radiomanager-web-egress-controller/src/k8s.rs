use k8s_openapi::api::batch::v1::Job;

#[derive(Clone)]
pub(crate) struct K8sClient {
    job_api: kube::Api<Job>,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum K8sClientError {
    #[error(transparent)]
    KubeClient(#[from] kube::Error),
}

impl K8sClient {
    pub(crate) async fn create(namespace: &str) -> Result<Self, K8sClientError> {
        let client = kube::Client::try_default().await?;
        let job_api: kube::Api<Job> = kube::Api::namespaced(client, namespace);

        Ok(Self { job_api })
    }
}
