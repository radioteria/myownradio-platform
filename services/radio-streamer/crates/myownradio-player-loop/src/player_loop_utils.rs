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
    TrackPackets(Vec<Packet>),
}

/// An iterator over the events that occur in the player loop.
pub struct PlayerLoopIter<C: NowPlayingClient> {
    player_loop: PlayerLoop<C>,
    previous_title: Option<String>,
    deferred_packets: Option<Vec<Packet>>,
}

impl<C: NowPlayingClient> PlayerLoopIter<C> {
    pub(crate) fn new(player_loop: PlayerLoop<C>) -> Self {
        Self {
            player_loop,
            previous_title: None,
            deferred_packets: None,
        }
    }
}

impl<C: NowPlayingClient> Iterator for PlayerLoopIter<C> {
    type Item = Result<PlayerLoopEvent, PlayerLoopError>;

    /// Advances the iterator and returns the next `PlayerLoopEvent`, if any.
    ///
    /// This method behaves as follows:
    /// * If there are deferred packets available, it returns a `PlayerLoopEvent::TrackPackets` event
    ///   containing the packets.
    /// * If there are no deferred packets but there are new packets available from the player loop,
    ///   it checks whether the title of the track has changed since the last iteration.
    ///   - If the title has changed, it returns a `PlayerLoopEvent::TrackTitle` event with the new
    ///     title and its running time, and stores the new packets as deferred packets for the next
    ///     iteration.
    ///   - If the title hasn't changed, it returns a `PlayerLoopEvent::TrackPackets` event containing
    ///     the new packets.
    /// * If there are no new packets, it returns `None`.
    ///
    /// # Examples
    ///
    /// ```
    /// use myownradio_player_loop::{NowPlayingClient, PlayerLoopEvent, PlayerLoopIter};
    ///
    /// fn process_events<C: NowPlayingClient>(iter: &mut PlayerLoopIter<C>) {
    ///     while let Some(event) = iter.next() {
    ///         match event {
    ///             Ok(PlayerLoopEvent::TrackTitle(title)) => println!("New track: {:?}", title),
    ///             Ok(PlayerLoopEvent::TrackPackets(packets)) => println!("Received packets: {:?}", packets),
    ///             Err(error) => println!("Error: {:?}", error),
    ///         }
    ///     }
    /// }
    /// ```
    fn next(&mut self) -> Option<Self::Item> {
        if let Some(packets) = self.deferred_packets.take() {
            return Some(Ok(PlayerLoopEvent::TrackPackets(packets)));
        }

        let packets = match self.player_loop.receive_next_audio_packets() {
            Ok(packets) => packets,
            Err(error) => return Some(Err(error)),
        };

        let curr_title = self.player_loop.current_title();
        let prev_title = self.previous_title.as_ref();

        if curr_title != prev_title {
            self.previous_title = curr_title.cloned();

            let running_time = (*self.player_loop.current_running_time()).into();

            self.deferred_packets.replace(packets);

            return Some(Ok(PlayerLoopEvent::TrackTitle(Title {
                pts: running_time,
                title: curr_title.cloned().unwrap(),
            })));
        }

        Some(Ok(PlayerLoopEvent::TrackPackets(packets)))
    }
}
