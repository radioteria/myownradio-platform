use crate::{NowPlayingClient, PlayerLoop, PlayerLoopError};
use myownradio_ffmpeg_utils::{Packet, Timestamp};
use std::time::Duration;
use tracing::trace;

/// Represents a track title with its presentation timestamp (PTS).
#[derive(Clone, Debug, PartialEq)]
pub struct Title {
    /// The PTS of the title.
    pub(crate) pts: Timestamp,
    /// The title string.
    pub(crate) title: String,
}

impl Title {
    pub fn title(&self) -> &str {
        &self.title
    }

    pub fn pts(&self) -> &Timestamp {
        &self.pts
    }

    pub fn pts_as_duration(&self) -> Duration {
        self.pts().into()
    }
}

/// Represents an event that can occur in the player loop.
#[derive(Clone, Debug, PartialEq)]
pub enum PlayerLoopEvent {
    /// The title of the track has changed.
    TrackTitle(Title),
    /// A new audio packet has been received.
    TrackPacket(Packet),
    /// Indicates that the player loop requires more data to continue looping.
    Continue,
}

/// An iterator over the events that occur in the player loop.
pub struct PlayerLoopIter<C: NowPlayingClient> {
    player_loop: PlayerLoop<C>,
    previous_title: Option<String>,
    packets_queue: Vec<Packet>,
}

impl<C: NowPlayingClient> PlayerLoopIter<C> {
    pub(crate) fn new(player_loop: PlayerLoop<C>) -> Self {
        Self {
            player_loop,
            previous_title: None,
            packets_queue: vec![],
        }
    }
}

impl<C: NowPlayingClient> Iterator for PlayerLoopIter<C> {
    type Item = Result<PlayerLoopEvent, PlayerLoopError>;

    /// Advances the iterator and returns the next event, if any.
    ///
    /// If the title of the track has changed since the last iteration, this method
    /// returns a `TrackTitle` event with the new title and its PTS. Otherwise, if there
    /// are packets in the packets queue, this method returns a `TrackPacket` event with
    /// the next packet from the queue. If there are no more packets in the queue and the
    /// end of the stream has been reached, this method returns `None`.
    /// ```
    fn next(&mut self) -> Option<Self::Item> {
        if self.packets_queue.is_empty() {
            trace!("Packets queue is empty: receiving next audio packets");
            match self.player_loop.receive_next_audio_packets() {
                Ok(packets) => {
                    self.packets_queue = packets;
                }
                Err(error) => return Some(Err(error)),
            }
        }

        let curr_title = self.player_loop.current_title();
        let prev_title = self.previous_title.as_ref();

        if curr_title != prev_title {
            self.previous_title = curr_title.cloned();

            let running_time = (*self.player_loop.current_running_time()).into();

            trace!("Current title has ben changed: emitting TrackTitle event");
            return Some(Ok(PlayerLoopEvent::TrackTitle(Title {
                pts: running_time,
                title: curr_title.cloned().unwrap(),
            })));
        }

        Some(Ok(match self.packets_queue.pop() {
            Some(packet) => {
                trace!("Received next packet: emitting TrackPacket event");
                PlayerLoopEvent::TrackPacket(packet)
            }
            None => {
                trace!("No packets received: emitting Continue event");
                PlayerLoopEvent::Continue
            }
        }))
    }
}
