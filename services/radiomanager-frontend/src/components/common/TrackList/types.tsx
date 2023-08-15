export interface TrackItem {
  trackId: number
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
