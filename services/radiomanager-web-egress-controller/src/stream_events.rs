use serde::Serialize;

#[derive(Serialize)]
pub(crate) enum StreamEvent {
    Starting,
    Started,
    Finishing,
    Finished,
    Failed,
    Stats { byte_count: u64, time_position: u64 },
}
