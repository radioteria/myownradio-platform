use crate::types::UserId;
use k8s_openapi::api::batch::v1::Job;
use kube::api::ListParams;

#[derive(Debug)]
pub(crate) struct StreamJob {
    pub(crate) name: String,
}

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
        let job_api = kube::Api::namespaced(client, namespace);

        Ok(Self { job_api })
    }

    fn create_stream_job_selector(&self, user_id: &UserId) -> String {
        format!(
            "radioterio-stream-job=true,radioterio-stream-user-id={}",
            **user_id
        )
    }

    pub(crate) async fn get_stream_jobs_by_user(
        &self,
        user_id: &UserId,
    ) -> Result<Vec<StreamJob>, K8sClientError> {
        let jobs = self
            .job_api
            .list(&ListParams::default().labels(&self.create_stream_job_selector(user_id)))
            .await?;

        Ok(jobs
            .items
            .into_iter()
            .map(|job| StreamJob {
                name: job.metadata.name.unwrap_or_default(),
            })
            .collect())
    }
}
