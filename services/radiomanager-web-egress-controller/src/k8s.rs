use crate::types::UserId;
use k8s_openapi::api::batch::v1::Job;
use k8s_openapi::apimachinery::pkg::apis::meta::v1::ObjectMeta;
use k8s_openapi::serde_json;
use kube::api::{DeleteParams, ListParams, PostParams, PropagationPolicy};
use tracing::info;

#[derive(Debug)]
pub(crate) struct StreamJob {
    pub(crate) name: String,
}

#[derive(Clone)]
pub(crate) struct K8sClient {
    job_api: kube::Api<Job>,

    image_name: String,
    image_tag: String,
}

#[derive(Debug, thiserror::Error)]
pub(crate) enum K8sClientError {
    #[error(transparent)]
    KubeClient(#[from] kube::Error),
}

impl K8sClient {
    pub(crate) async fn create(
        namespace: &str,
        image_name: &str,
        image_tag: &str,
    ) -> Result<Self, K8sClientError> {
        let client = kube::Client::try_default().await?;
        let job_api = kube::Api::namespaced(client, namespace);

        Ok(Self {
            job_api,
            image_name: image_name.to_string(),
            image_tag: image_tag.to_string(),
        })
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

    pub(crate) async fn create_stream_job(
        &self,
        stream_id: &str,
        user_id: &UserId,
        webpage_url: &str,
        rtmp_url: &str,
        rtmp_stream_key: &str,
    ) -> Result<(), K8sClientError> {
        let labels = serde_json::json!({
            "radioterio-stream-job": "true",
            "radioterio-stream-user-id": format_args!("{}", **user_id),
            "radioterio-stream-id": stream_id,
        });

        let egress_container_manifest = serde_json::json!({
          "name": "web-egress-process",
          "image": format_args!("{}:{}", self.image_name, self.image_tag),
          "securityContext": {
            "privileged": true
          },
          "volumeMounts": [{
            "name": "dev-dri",
            "mountPath": "/dev/dri"
          }],
          "resources": {
            "requests": {
              "memory": "512Mi",
              "cpu": "500m"
            },
            "limits": {
               "memory": "1024Mi",
               "cpu": "1500m"
            }
          },
          "env": [
            {
              "name": "WEBPAGE_URL",
              "value": webpage_url
            },
            {
              "name": "RTMP_URL",
              "value": rtmp_url
            },
            {
              "name": "RTMP_STREAM_KEY",
              "value": rtmp_stream_key
            },
            {
              "name": "VIDEO_WIDTH",
              "value": "1280"
            },
            {
              "name": "VIDEO_HEIGHT",
              "value": "720"
            },
            {
              "name": "VIDEO_BITRATE",
              "value": "2500"
            },
            {
              "name": "VIDEO_FRAMERATE",
              "value": "30"
            },
            {
              "name": "AUDIO_BITRATE",
              "value": "128"
            },
            {
              "name": "CEF_GPU_ENABLED",
              "value": "true"
            },
            {
              "name": "VIDEO_ACCELERATION",
              "value": "VAAPI"
            },
          ]
        });

        let job_manifest = serde_json::json!({
          "apiVersion": "batch/v1",
          "kind": "Job",
          "metadata": {
            "name": format_args!("radioterio-stream-{}", stream_id),
            "labels": labels,
          },
          "spec": {
            "completions": 1,
            "parallelism": 1,
            "template": {
              "spec": {
                "containers": [
                  egress_container_manifest
                ],
                "volumes": [{
                  "name": "dev-dri",
                  "hostPath": {
                    "path": "/dev/dri"
                  }
                }],
                "restartPolicy": "OnFailure"
              }
            }
          }
        }
        );

        let job = self
            .job_api
            .create(
                &PostParams::default(),
                &serde_json::from_value(job_manifest).expect("Unable to parse job manifest"),
            )
            .await?;

        info!("Created job: {:?}", job);

        Ok(())
    }

    pub(crate) async fn delete_stream_job(
        &self,
        stream_id: &str,
        user_id: &UserId,
    ) -> Result<(), K8sClientError> {
        let label_selector = format!(
            "radioterio-stream-id={},radioterio-stream-user-id={}",
            stream_id, **user_id
        );
        let job = self
            .job_api
            .list(&ListParams::default().labels(&label_selector))
            .await?
            .into_iter()
            .next();

        if let Some(name) = &job.unwrap().metadata.name {
            let _ = self
                .job_api
                .delete(
                    name,
                    &DeleteParams {
                        propagation_policy: Some(PropagationPolicy::Foreground),
                        ..DeleteParams::default()
                    },
                )
                .await?;
        }

        Ok(())
    }

    pub(crate) async fn get_stream_job(
        &self,
        stream_id: &str,
        user_id: &UserId,
    ) -> Result<Option<StreamJob>, K8sClientError> {
        let label_selector = format!(
            "radioterio-stream-id={},radioterio-stream-user-id={}",
            stream_id, **user_id
        );
        let stream_job = self
            .job_api
            .list(&ListParams::default().labels(&label_selector))
            .await?
            .into_iter()
            .next()
            .map(|job| StreamJob {
                name: job.metadata.name.unwrap(),
            });

        Ok(stream_job)
    }
}
