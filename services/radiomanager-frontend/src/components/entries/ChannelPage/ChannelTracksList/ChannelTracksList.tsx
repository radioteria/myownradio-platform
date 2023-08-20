'use client'

import { TrackList } from '@/components/common/TrackList'
import { useNowPlaying } from '@/modules/NowPlaying'
import { UserChannelTrack } from '@/api/api.types'

interface ChannelTrackEntry {
  trackId: number
  title: string
  artist: string
  album: string
  duration: number
}

export const toChannelTrackEntry = (track: UserChannelTrack): ChannelTrackEntry => ({
  trackId: track.tid,
  title: track.title || track.filename,
  artist: track.artist,
  album: track.album,
  duration: track.duration,
})

interface Props {
  tracks: readonly ChannelTrackEntry[]
  tracksCount: number
  channelId: number
}

export const ChannelTracksList: React.FC<Props> = ({ tracks, channelId }) => {
  const { nowPlaying } = useNowPlaying()
  const currentTrack = nowPlaying
    ? {
        index: nowPlaying.playlistPosition - 1,
        position: nowPlaying.currentTrack.offset,
        duration: nowPlaying.currentTrack.duration,
      }
    : null

  return (
    <section className={'h-full'}>
      <TrackList tracks={tracks} currentTrack={currentTrack} />
    </section>
  )
}
