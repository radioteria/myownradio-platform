'use client'

import { UserChannelTrack } from '@/api/api.types'
import { useNowPlaying } from '@/hooks/useNowPlaying'
import { TrackList } from '@/components/common/TrackList'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'

interface Props {
  tracks: readonly UserChannelTrack[]
  tracksCount: number
  channelId: number
}

export const ChannelTracksList: React.FC<Props> = ({ tracks, channelId }) => {
  const { nowPlaying } = useNowPlaying(channelId)

  return (
    <section>
      <TrackList
        tracks={tracks.map((track, index) => ({
          trackId: track.tid,
          title: track.title || track.filename,
          artist: track.artist,
          album: track.album,
          duration: track.duration,
        }))}
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
