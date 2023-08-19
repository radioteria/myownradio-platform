'use client'

import { UserChannelTrack } from '@/api/api.types'
import { TrackList } from '@/components/common/TrackList'
import { useMemo } from 'react'
import { useNowPlaying } from '@/modules/NowPlaying'

interface Props {
  tracks: readonly UserChannelTrack[]
  tracksCount: number
  channelId: number
}

export const ChannelTracksList: React.FC<Props> = ({ tracks, channelId }) => {
  const memoizedTracks = useMemo(
    () =>
      tracks.map((track, index) => ({
        trackId: track.tid,
        title: track.title || track.filename,
        artist: track.artist,
        album: track.album,
        duration: track.duration,
      })),
    [tracks],
  )
  const { nowPlaying } = useNowPlaying()

  return (
    <section className={'h-full'}>
      <TrackList
        tracks={memoizedTracks}
        currentTrack={
          nowPlaying
            ? {
                index: nowPlaying.playlistPosition - 1,
                position: nowPlaying.currentTrack.offset,
                duration: nowPlaying.currentTrack.duration,
              }
            : null
        }
      />
    </section>
  )
}
