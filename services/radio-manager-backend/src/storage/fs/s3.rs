use crate::storage::fs::{FileSystem, FileSystemError, FileSystemResult};
use crate::utils::TeeResultUtils;
use async_trait::async_trait;
use rusoto_core::credential::StaticProvider;
use rusoto_core::{HttpClient, RusotoError};
use rusoto_s3::{DeleteObjectRequest, S3Client, S3};
use serde::de::StdError;
use tracing::error;

#[derive(Clone)]
pub(crate) struct S3FileSystem {
    s3_bucket: String,
    s3_client: S3Client,
}

impl<T: StdError> From<RusotoError<T>> for FileSystemError {
    fn from(error: RusotoError<T>) -> Self {
        FileSystemError::Unknown(format!("{:?}", error))
    }
}

impl S3FileSystem {
    pub(crate) fn create(
        s3_region: &str,
        s3_bucket: &str,
        access_key_id: &str,
        secret_access_key: &str,
    ) -> Self {
        let credentials_provider = StaticProvider::new(
            access_key_id.to_string(),
            secret_access_key.to_string(),
            None,
            None,
        );
        let request_dispatcher = HttpClient::new().unwrap();
        let s3_client = S3Client::new_with(
            request_dispatcher,
            credentials_provider,
            s3_region.parse().unwrap(),
        );

        Self {
            s3_bucket: s3_region.to_string(),
            s3_client,
        }
    }
}

#[async_trait]
impl FileSystem for S3FileSystem {
    async fn delete_file(&self, path: &str) -> FileSystemResult<()> {
        let path = path.to_string();

        self.s3_client
            .delete_object(DeleteObjectRequest {
                bucket: self.s3_bucket.clone(),
                key: path.to_string(),
                ..DeleteObjectRequest::default()
            })
            .await
            .tee_err(|error| error!(?error, "Unable to delete file from s3"))?;

        Ok(())
    }
}
