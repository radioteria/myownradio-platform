import { useRef } from 'react'
import { UserChannelTrack } from '@/api'
import { TrackList } from '@/components/shared/TrackList'
import { useNowPlaying } from '@/modules/NowPlaying'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'

import type { ChannelTrackEntry as ApiChannelTrackEntry } from '@/api/radiomanager'

export interface ChannelTrackEntry {
  trackId: number
  uniqueId: string
  title: string
  artist: string
  album: string
  duration: number
}

export const toChannelTrackEntry = (track: UserChannelTrack): ChannelTrackEntry => ({
  trackId: track.tid,
  uniqueId: track.uniqueId,
  title: track.title || track.filename,
  artist: track.artist ?? '',
  album: track.album ?? '',
  duration: track.duration,
})

export const toChannelTrackEntry2 = ({
  track,
  entry,
}: ApiChannelTrackEntry): ChannelTrackEntry => ({
  trackId: track.tid,
  uniqueId: entry.uniqueId,
  title: track.title || track.filename,
  artist: track.artist ?? '',
  album: track.album ?? '',
  duration: track.duration,
})

interface Props {
  readonly channelId: number
  readonly tracks: readonly (ChannelTrackEntry | null)[]
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
  readonly onRemoveTracksFromChannel: (uniqueIds: readonly string[]) => void
  readonly loadMoreTracks: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

export const ChannelTracksList: React.FC<Props> = ({
  channelId,
  tracks,
  onDeleteTracks,
  onRemoveTracksFromChannel,
  loadMoreTracks,
}) => {
  const { nowPlaying } = useNowPlaying()
  const currentTrack = nowPlaying
    ? {
        index: nowPlaying.playlistPosition - 1,
        position: nowPlaying.currentTrack.offset,
        duration: nowPlaying.currentTrack.duration,
      }
    : null

  const contextMenu = useContextMenu()
  const contextMenuRef = useRef(null)

  const handleTracksListMenu = (
    selectedTracks: readonly ChannelTrackEntry[],
    event: React.MouseEvent<HTMLElement>,
  ) => {
    if (selectedTracks.length === 0) {
      return
    }

    contextMenu.show({
      menuItems: [
        {
          onClick: () => {
            onRemoveTracksFromChannel(selectedTracks.map(({ uniqueId }) => uniqueId))
          },
          type: MenuItemType.Item,
          label: 'Remove from this channel',
        },
        {
          onClick: () => {
            onDeleteTracks(selectedTracks.map(({ trackId }) => trackId))
          },
          type: MenuItemType.Item,
          label: 'Remove from your library',
        },
      ],
      portalElement: contextMenuRef?.current ?? undefined,
      position: {
        x: event.clientX,
        y: event.clientY,
      },
    })
  }

  return (
    <section className={'h-full'}>
      <TrackList
        trackItems={tracks}
        currentTrack={currentTrack}
        onTrackListMenu={handleTracksListMenu}
        contextMenuRef={contextMenuRef}
        loadMoreTrackItems={loadMoreTracks}
      />
    </section>
  )
}
