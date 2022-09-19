use crate::storage::fs::FileSystem;
use crate::utils::TeeResultUtils;
use async_trait::async_trait;
use tracing::error;

#[derive(Clone)]
pub(crate) struct LocalFileSystem {
    root_path: String,
}

impl LocalFileSystem {
    pub(crate) fn create(root_path: String) -> Self {
        Self { root_path }
    }
}

#[async_trait]
impl FileSystem for LocalFileSystem {
    async fn delete_file(&self, path: &str) -> std::io::Result<()> {
        let root_path = self.root_path.clone();
        let path = path.to_string();

        actix_rt::task::spawn_blocking(move || {
            std::fs::remove_file(format!("{}/{}", root_path, path))
        })
        .await
        .expect("Unable to spawn blocking task")
        .tee_err(|error| error!(?error, "Unable to delete file from local file system"))?;

        Ok(())
    }
}
