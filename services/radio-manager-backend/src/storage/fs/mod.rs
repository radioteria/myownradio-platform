pub(crate) mod local;
pub(crate) mod utils;

use async_trait::async_trait;

#[async_trait]
pub(crate) trait FileSystem {
    async fn delete_file(&self, path: &str) -> std::io::Result<()>;

    async fn get_file_contents(
        &self,
        path: &str,
    ) -> std::io::Result<futures::channel::mpsc::Receiver<Vec<u8>>>;
}
