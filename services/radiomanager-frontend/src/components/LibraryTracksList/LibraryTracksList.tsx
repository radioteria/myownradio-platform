'use client'

import { UserTrack } from '@/api/api.types'
import { TrackList } from '@/components/common/TrackList'

interface Props {
  tracks: readonly UserTrack[]
  tracksCount: number
}

export const LibraryTracksList: React.FC<Props> = ({ tracks, tracksCount }) => {
  return (
    <section>
      <TrackList
        tracks={tracks.map((track) => ({
          trackId: track.tid,
          title: track.title || track.filename,
          artist: track.artist ?? '',
          album: track.album ?? '',
          duration: track.duration,
        }))}
        currentTrack={null}
      />
    </section>
  )
}
