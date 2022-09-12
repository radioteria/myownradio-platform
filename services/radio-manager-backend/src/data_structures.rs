use serde_repr::{Deserialize_repr, Serialize_repr};

#[derive(Serialize_repr, Deserialize_repr, Debug)]
#[repr(u8)]
pub(crate) enum SortingColumn {
    TrackId,
    Title,
    Artist,
    Genre,
    Duration,
}

impl Default for SortingColumn {
    fn default() -> Self {
        Self::TrackId
    }
}

impl SortingColumn {
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingColumn::TrackId => "`r_tracks`.`tid`",
            SortingColumn::Title => "`r_tracks`.`title`",
            SortingColumn::Artist => "`r_tracks`.`artist`",
            SortingColumn::Genre => "`r_tracks`.`genre`",
            SortingColumn::Duration => "`r_tracks`.`duration`",
        }
    }
}

#[derive(Serialize_repr, Deserialize_repr, Debug)]
#[repr(u8)]
pub(crate) enum SortingOrder {
    Desc,
    Asc,
}

impl Default for SortingOrder {
    fn default() -> Self {
        Self::Desc
    }
}

impl SortingOrder {
    pub(crate) fn as_str(&self) -> &str {
        match self {
            SortingOrder::Desc => "DESC",
            SortingOrder::Asc => "ASC",
        }
    }
}

// Copied from Defaults.php
pub(crate) const DEFAULT_TRACKS_PER_REQUEST: i64 = 50;
