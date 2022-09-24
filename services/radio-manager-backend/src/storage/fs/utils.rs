use crate::storage::db::repositories::FileRow;

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
