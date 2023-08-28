import { UserTrack } from '@/api/api.types'
import { TracksList } from '@/components/common/TrackList'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import AnimatedBars from '@/icons/AnimatedBars'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'
import { useRef } from 'react'

interface LibraryTrackEntry {
  trackId: number
  channelTrackId: null
  title: string
  artist: string
  album: string
  duration: number
}

export const toLibraryTrackEntry = (track: UserTrack): LibraryTrackEntry => ({
  trackId: track.tid,
  channelTrackId: null,
  title: track.title || track.filename,
  artist: track.artist ?? '',
  album: track.album ?? '',
  duration: track.duration,
})

interface Props {
  readonly tracks: readonly LibraryTrackEntry[]
  readonly canInfinitelyScroll: boolean
  readonly onInfiniteScroll: () => void
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
}

export const LibraryTracksList: React.FC<Props> = ({
  tracks,
  canInfinitelyScroll,
  onInfiniteScroll,
  onDeleteTracks,
}) => {
  const contextMenu = useContextMenu()
  const contextMenuRef = useRef(null)

  const handleTracksListMenu = (
    selectedTracks: readonly LibraryTrackEntry[],
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
        currentTrack={null}
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
