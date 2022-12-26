pub(crate) mod local;
pub(crate) mod s3;
pub(crate) mod utils;

use crate::config::StorageConfig;
use async_trait::async_trait;

#[async_trait]
pub(crate) trait FileSystem {
    async fn delete_file(&self, path: &str) -> FileSystemResult<()>;
}

#[derive(thiserror::Error, Debug)]
pub(crate) enum FileSystemError {
    #[error("Unknown file system error: {0}")]
    Unknown(String),
}

pub(crate) type FileSystemResult<T> = Result<T, FileSystemError>;

pub(crate) fn create_file_system(
    config: &StorageConfig,
) -> Box<dyn FileSystem + Sync + Send + 'static> {
    match config {
        StorageConfig::Local {
            file_system_root_path,
            ..
        } => Box::new(local::LocalFileSystem::create(
            file_system_root_path.clone(),
        )),
        StorageConfig::S3 {
            s3_region,
            s3_bucket,
            s3_access_key,
            s3_secret_key,
        } => Box::new(s3::S3FileSystem::create(
            s3_region,
            s3_bucket,
            s3_access_key,
            s3_secret_key,
        )),
    }
}
