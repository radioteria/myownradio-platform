use crate::k8s_utils::{make_stream_job_name, make_stream_job_selector};
use crate::types::{AudioSettings, RtmpSettings, UserId, VideoSettings};
use either::Either;
use k8s_openapi::api::batch::v1::Job;
use k8s_openapi::api::core::v1::Pod;
use k8s_openapi::serde_json;
use kube::api::{DeleteParams, ListParams, PostParams};
use kube::ResourceExt;
use serde::Serialize;
use std::collections::HashMap;
use tracing::{debug, error, instrument};

#[derive(Debug, Serialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct StreamJob {
    pub(crate) channel_id: u32,
    pub(crate) status: StreamJobStatus,
}

#[derive(Debug, Serialize)]
pub(crate) enum StreamJobStatus {
    Starting,
    Running,
    Error,
}

#[derive(Clone)]
pub(crate) struct K8sClient {
    job_api: kube::Api<Job>,
    pod_api: kube::Api<Pod>,

    image_name: String,
    image_tag: String,

    radiomanager_backend_endpoint: String,
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
        radiomanager_backend_endpoint: &str,
    ) -> Result<Self, K8sClientError> {
        let client = kube::Client::try_default().await?;
        let job_api = kube::Api::namespaced(client.clone(), namespace);
        let pod_api = kube::Api::namespaced(client, namespace);

        Ok(Self {
            job_api,
            pod_api,
            image_name: image_name.to_string(),
            image_tag: image_tag.to_string(),
            radiomanager_backend_endpoint: radiomanager_backend_endpoint.to_string(),
        })
    }

    pub(crate) async fn get_stream_jobs_by_user(
        &self,
        user_id: &UserId,
    ) -> Result<Vec<StreamJob>, K8sClientError> {
        let job_to_pod_map = self
            .pod_api
            .list(&ListParams::default())
            .await?
            .into_iter()
            .filter_map(|pod| Some((pod.labels().get("job-name").cloned()?, pod)))
            .collect::<HashMap<_, _>>();

        let labels_selector = make_stream_job_selector(user_id);
        let user_stream_jobs = self
            .job_api
            .list(&ListParams::default().labels(&labels_selector))
            .await?;

        Ok(user_stream_jobs
            .items
            .into_iter()
            .map(|job| {
                let pod_status = job_to_pod_map
                    .get(&job.name_any())
                    .and_then(|pod| pod.status.clone())
                    .and_then(|status| status.phase);

                StreamJob {
                    channel_id: job
                        .labels()
                        .get("radioterio-stream-channel-id")
                        .and_then(|str| str.parse().ok())
                        .unwrap_or_default(),
                    status: match pod_status.as_ref().map(AsRef::as_ref) {
                        Some("Pending") => StreamJobStatus::Starting,
                        Some("Running") => StreamJobStatus::Running,
                        _ => StreamJobStatus::Error,
                    },
                }
            })
            .collect())
    }

    pub(crate) async fn create_stream_job(
        &self,
        user_id: &UserId,
        stream_id: &str,
        channel_id: &u32,
        webpage_url: &str,
        rtmp_settings: &RtmpSettings,
        video_settings: &VideoSettings,
        audio_settings: &AudioSettings,
    ) -> Result<(), K8sClientError> {
        let job_name = make_stream_job_name(user_id, channel_id);

        let labels = serde_json::json!({
            "radioterio-stream-user-id": format_args!("{}", **user_id),
            "radioterio-stream-channel-id": format_args!("{}", channel_id)
        });

        let env = serde_json::json!([
          {
            "name": "WEBPAGE_URL",
            "value": webpage_url
          },
          {
            "name": "CHANNEL_ID",
            "value": channel_id
          },
          {
            "name": "USER_ID",
            "value": format_args!("{}", **user_id)
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
          {
              "name": "RADIOMANAGER_BACKEND_ENDPOINT",
              "value": self.radiomanager_backend_endpoint
          }
        ]);

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
          "env": env
        });

        let job_manifest = serde_json::json!({
          "apiVersion": "batch/v1",
          "kind": "Job",
          "metadata": {
            "name": job_name,
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

        self.job_api
            .create(
                &PostParams::default(),
                &serde_json::from_value(job_manifest).expect("Unable to parse stream job manifest"),
            )
            .await?;

        Ok(())
    }

    #[instrument(skip(self))]
    pub(crate) async fn delete_stream_job(
        &self,
        user_id: &UserId,
        channel_id: &u32,
    ) -> Result<(), K8sClientError> {
        let job_name = make_stream_job_name(user_id, channel_id);

        let result = self
            .job_api
            .delete(&job_name, &DeleteParams::foreground())
            .await?;

        match result {
            Either::Left(job) => {
                debug!("Stream job deletion has started: {:?}", job.status)
            }
            Either::Right(status) => {
                debug!("Stream job deleted: {:?}", status)
            }
        }

        let label_selector = format!("job-name={}", job_name);
        let pods = self
            .pod_api
            .list(&ListParams::default().labels(&label_selector))
            .await?;

        for pod in pods.items.iter() {
            match self
                .pod_api
                .delete(pod.name_any().as_ref(), &DeleteParams::background())
                .await
            {
                Ok(Either::Left(pod)) => {
                    debug!("Stream pod deletion has started: {:?}", pod.status)
                }
                Ok(Either::Right(status)) => {
                    debug!("Stream pod deleted: {:?}", status)
                }
                Err(error) => {
                    error!("Stream pod deletion failed: {:?}", error);
                }
            }
        }

        Ok(())
    }

    pub(crate) async fn get_stream_job(
        &self,
        user_id: &UserId,
        channel_id: &u32,
    ) -> Result<StreamJob, K8sClientError> {
        let job_name = make_stream_job_name(user_id, channel_id);
        let job = self.job_api.get(&job_name).await?;

        let label_selector = format!("job-name={}", job_name);
        let pod_status = self
            .pod_api
            .list(&ListParams::default().labels(&label_selector))
            .await?
            .into_iter()
            .next()
            .and_then(|pod| pod.status)
            .and_then(|status| status.phase);

        Ok(StreamJob {
            channel_id: job
                .labels()
                .get("radioterio-stream-channel-id")
                .and_then(|str| str.parse().ok())
                .unwrap_or_default(),
            status: match pod_status.as_ref().map(AsRef::as_ref) {
                Some("Pending") => StreamJobStatus::Starting,
                Some("Running") => StreamJobStatus::Running,
                _ => StreamJobStatus::Error,
            },
        })
    }
}
