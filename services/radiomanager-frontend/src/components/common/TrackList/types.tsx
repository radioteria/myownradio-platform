export interface TrackItem {
  trackId: number
  channelTrackId: string | null
  title: string
  artist: string | null
  album: string
  duration: number
}

export interface CurrentTrack {
  index: number
  position: number
  duration: number
}
