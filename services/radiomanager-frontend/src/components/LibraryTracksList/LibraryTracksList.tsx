'use client'

import { UserTrack } from '@/api/api.types'
import { TrackList } from '@/components/common/TrackList'
import { useMemo } from 'react'

interface Props {
  tracks: readonly UserTrack[]
  tracksCount: number
}

export const LibraryTracksList: React.FC<Props> = ({ tracks, tracksCount }) => {
  const memoizedTracks = useMemo(
    () =>
      tracks.map((track) => ({
        trackId: track.tid,
        title: track.title || track.filename,
        artist: track.artist ?? '',
        album: track.album ?? '',
        duration: track.duration,
      })),
    [tracks],
  )

  return (
    <section>
      <TrackList tracks={memoizedTracks} currentTrack={null} />
    </section>
  )
}
