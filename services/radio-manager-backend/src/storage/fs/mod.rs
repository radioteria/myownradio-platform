pub(crate) mod local;
pub(crate) mod utils;

use async_trait::async_trait;

#[async_trait]
pub(crate) trait FileSystem {
    async fn delete_file(&self, path: &str) -> std::io::Result<()>;
}
