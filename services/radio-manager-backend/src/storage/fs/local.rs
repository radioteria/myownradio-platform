use crate::storage::fs::FileSystem;
use crate::utils::TeeResultUtils;
use async_trait::async_trait;
use futures::SinkExt;
use std::io::Read;
use tokio::fs;
use tokio::io::AsyncReadExt;
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

        fs::remove_file(format!("{}/{}", root_path, path))
            .await
            .tee_err(|error| error!(?error, "Unable to delete file from local file system"))?;

        Ok(())
    }

    async fn get_file_contents(
        &self,
        path: &str,
    ) -> std::io::Result<futures::channel::mpsc::Receiver<Vec<u8>>> {
        let (tx, rx) = futures::channel::mpsc::channel(0);

        let root_path = self.root_path.clone();
        let path = path.to_string();

        let file = fs::File::open(format!("{}/{}", root_path, path)).await?;

        actix_rt::spawn(async move {
            let mut tx = tx;
            let mut file = file;

            let mut buffer = [0u8; 8192];

            loop {
                let size = match file.read(&mut buffer).await {
                    Ok(size) => size,
                    Err(error) => {
                        error!(?error, "Error reading chunk from file");
                        return;
                    }
                };

                if size == 0 {
                    // EOF reached
                    break;
                }

                if let Err(error) = tx.send(buffer[..size].to_vec()).await {
                    error!(?error, "Error sending chunk through channel");
                    return;
                }
            }
        });

        Ok(rx)
    }
}
