use crate::types::{AudioSettings, RtmpSettings, UserId, VideoSettings};
use k8s_openapi::api::batch::v1::Job;
use k8s_openapi::api::core::v1::Pod;
use k8s_openapi::serde_json;
use kube::api::{DeleteParams, ListParams, PostParams, PropagationPolicy};
use kube::ResourceExt;
use serde::Serialize;
use std::collections::HashMap;
use tracing::{error, info};

#[derive(Debug, Serialize)]
pub(crate) struct StreamJob {
    pub(crate) channel_id: String,
    pub(crate) stream_id: String,
    pub(crate) status: StreamJobStatus,
}

#[derive(Debug, Serialize)]
pub(crate) enum StreamJobStatus {
    Starting,
    Running,
    Finished,
    Failed,
    Unknown,
}

#[derive(Clone)]
pub(crate) struct K8sClient {
    job_api: kube::Api<Job>,
    pod_api: kube::Api<Pod>,

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
        let job_api = kube::Api::namespaced(client.clone(), namespace);
        let pod_api = kube::Api::namespaced(client, namespace);

        Ok(Self {
            job_api,
            pod_api,
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
        let pod_to_job_map = self
            .pod_api
            .list(&ListParams::default())
            .await?
            .into_iter()
            .map(|pod| (pod.labels().get("job-name").cloned(), pod))
            .collect::<HashMap<_, _>>();

        let jobs = self
            .job_api
            .list(&ListParams::default().labels(&self.create_stream_job_selector(user_id)))
            .await?;

        Ok(jobs
            .items
            .into_iter()
            .map(|job| {
                let pod_status = pod_to_job_map
                    .get(&Some(job.name_any()))
                    .and_then(|pod| pod.status.clone())
                    .and_then(|status| status.phase);

                StreamJob {
                    stream_id: job
                        .labels()
                        .get("radioterio-stream-id")
                        .cloned()
                        .unwrap_or_default(),
                    channel_id: job
                        .labels()
                        .get("radioterio-stream-channel-id")
                        .cloned()
                        .unwrap_or_default(),
                    status: match pod_status.as_ref().map(AsRef::as_ref) {
                        Some("Pending") => StreamJobStatus::Starting,
                        Some("Running") => StreamJobStatus::Running,
                        Some("Succeeded") => StreamJobStatus::Finished,
                        Some("Failed") => StreamJobStatus::Failed,
                        _ => StreamJobStatus::Unknown,
                    },
                }
            })
            .collect())
    }

    pub(crate) async fn create_stream_job(
        &self,
        stream_id: &str,
        channel_id: &str,
        user_id: &UserId,
        webpage_url: &str,
        rtmp_settings: &RtmpSettings,
        video_settings: &VideoSettings,
        audio_settings: &AudioSettings,
    ) -> Result<(), K8sClientError> {
        let labels = serde_json::json!({
            "radioterio-stream-job": "true",
            "radioterio-stream-user-id": format_args!("{}", **user_id),
            "radioterio-stream-id": stream_id,
            "radioterio-stream-channel-id": channel_id
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
              "value": rtmp_settings.rtmp_url
            },
            {
              "name": "RTMP_STREAM_KEY",
              "value": rtmp_settings.stream_key
            },
            {
              "name": "VIDEO_WIDTH",
              "value": format_args!("{}", video_settings.width)
            },
            {
              "name": "VIDEO_HEIGHT",
              "value": format_args!("{}", video_settings.height)
            },
            {
              "name": "VIDEO_BITRATE",
              "value": format_args!("{}", video_settings.bitrate)
            },
            {
              "name": "VIDEO_FRAMERATE",
              "value": format_args!("{}", video_settings.framerate)
            },
            {
              "name": "AUDIO_BITRATE",
              "value": format_args!("{}", audio_settings.bitrate)
            },
            {
              "name": "AUDIO_CHANNELS",
              "value": format_args!("{}", audio_settings.channels)
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
            "name": format_args!("radioterio-stream-{}-{}", **user_id, channel_id),
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
        channel_id: &str,
        user_id: &UserId,
    ) -> Result<bool, K8sClientError> {
        let label_selector = format!(
            "radioterio-stream-channel-id={},radioterio-stream-user-id={}",
            channel_id, **user_id
        );
        let job = self
            .job_api
            .list(&ListParams::default().labels(&label_selector))
            .await?
            .into_iter()
            .next();

        match job {
            Some(job) => {
                let r = self
                    .job_api
                    .delete(
                        &job.name_any(),
                        &DeleteParams {
                            propagation_policy: Some(PropagationPolicy::Foreground),
                            ..DeleteParams::default()
                        },
                    )
                    .await?;

                error!("Unable to stop the stream: {:?}", r);
            }
            None => return Ok(false),
        }

        Ok(true)
    }

    pub(crate) async fn get_stream_job(
        &self,
        channel_id: &str,
        user_id: &UserId,
    ) -> Result<Option<StreamJob>, K8sClientError> {
        let label_selector = format!(
            "radioterio-stream-channel-id={},radioterio-stream-user-id={}",
            channel_id, **user_id
        );

        let job = self
            .job_api
            .list(&ListParams::default().labels(&label_selector))
            .await?
            .into_iter()
            .next();

        let pod = match job.as_ref() {
            Some(job) => self
                .pod_api
                .list(&ListParams::default().labels(&format!("job-name={}", job.name_any())))
                .await?
                .into_iter()
                .next(),
            None => None,
        };

        let pod_status = pod
            .and_then(|pod| pod.status)
            .and_then(|status| status.phase);

        let stream_job = job.map(|job| StreamJob {
            stream_id: job
                .labels()
                .get("radioterio-stream-id")
                .cloned()
                .unwrap_or_default(),
            channel_id: job
                .labels()
                .get("radioterio-stream-channel-id")
                .cloned()
                .unwrap_or_default(),
            status: match pod_status.as_ref().map(AsRef::as_ref) {
                Some("Pending") => StreamJobStatus::Starting,
                Some("Running") => StreamJobStatus::Running,
                Some("Succeeded") => StreamJobStatus::Finished,
                Some("Failed") => StreamJobStatus::Failed,
                _ => StreamJobStatus::Unknown,
            },
        });

        Ok(stream_job)
    }
}
