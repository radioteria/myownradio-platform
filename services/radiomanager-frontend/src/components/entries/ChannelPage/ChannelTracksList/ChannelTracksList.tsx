import { useRef } from 'react'
import { UserChannelTrack } from '@/api'
import { TracksList } from '@/components/common/TrackList'
import { useNowPlaying } from '@/modules/NowPlaying'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'

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

interface Props {
  readonly totalTracks: number
  readonly tracks: readonly ChannelTrackEntry[]
  readonly channelId: number
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
  readonly onRemoveTracksFromChannel: (uniqueIds: readonly string[]) => void
}

export const ChannelTracksList: React.FC<Props> = ({
  totalTracks,
  tracks,
  channelId,
  onDeleteTracks,
  onRemoveTracksFromChannel,
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
      <TracksList
        totalTracks={totalTracks}
        tracks={tracks}
        currentTrack={currentTrack}
        onTracksListMenu={handleTracksListMenu}
        contextMenuRef={contextMenuRef}
        onReachUnloadedTrack={(trackIndex) => {}}
        loadMoreItems={() => Promise.resolve()}
      />
    </section>
  )
}
