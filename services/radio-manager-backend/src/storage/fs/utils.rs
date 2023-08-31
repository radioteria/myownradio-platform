use crate::storage::db::repositories::FileRow;
use async_fs::File;
use bytes::Bytes;
use futures::channel::mpsc;
use futures::{AsyncReadExt, SinkExt};
use std::io;

pub(crate) trait GetPath {
    fn get_path(&self) -> String;
}

impl GetPath for FileRow {
    fn get_path(&self) -> String {
        format!(
            "{}/{}/{}.{}",
            &self.file_hash[..1],
            &self.file_hash[1..2],
            self.file_hash,
            self.file_extension
        )
    }
}

pub(crate) async fn read_file_to_channel(
    file_path: &str,
    mut tx: mpsc::Sender<Bytes>,
) -> Result<(), io::Error> {
    let mut file = File::open(file_path).await?;
    let mut buffer = [0u8; 1024];

    loop {
        let bytes_read = file.read(&mut buffer).await?;

        if bytes_read == 0 {
            break; // EOF
        }

        if tx
            .send(Bytes::copy_from_slice(&buffer[0..bytes_read]))
            .await
            .is_err()
        {
            break;
        }
    }

    Ok(())
}
