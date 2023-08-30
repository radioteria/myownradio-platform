import { TracksList } from '@/components/common/TrackList'
import { useNowPlaying } from '@/modules/NowPlaying'
import { UserChannelTrack } from '@/api/apiTypes'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import AnimatedBars from '@/icons/AnimatedBars'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'
import { useRef } from 'react'

interface ChannelTrackEntry {
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
  artist: track.artist,
  album: track.album,
  duration: track.duration,
})

interface Props {
  readonly tracks: readonly ChannelTrackEntry[]
  readonly channelId: number
  readonly canInfinitelyScroll: boolean
  readonly onInfiniteScroll: () => void
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
  readonly onRemoveTracksFromChannel: (uniqueIds: readonly string[]) => void
}

export const ChannelTracksList: React.FC<Props> = ({
  tracks,
  channelId,
  canInfinitelyScroll,
  onInfiniteScroll,
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
    contextMenu.show({
      menuItems: [
        {
          onClick: () => {
            onDeleteTracks(selectedTracks.map(({ trackId }) => trackId))
          },
          type: MenuItemType.Item,
          label: 'Remove from your library',
        },
        {
          onClick: () => {
            onRemoveTracksFromChannel(selectedTracks.map(({ uniqueId }) => uniqueId))
          },
          type: MenuItemType.Item,
          label: 'Remove from this channel',
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
        tracks={tracks}
        currentTrack={currentTrack}
        onTracksListMenu={handleTracksListMenu}
        contextMenuRef={contextMenuRef}
      />
      {canInfinitelyScroll && (
        <InfiniteScroll key={tracks.length} offset={200} onReach={onInfiniteScroll}>
          <div className={'text-center p-2'}>
            <AnimatedBars size={32} />
          </div>
        </InfiniteScroll>
      )}
    </section>
  )
}
