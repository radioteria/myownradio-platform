import { UserTrack } from '@/api'
import { TracksList } from '@/components/common/TrackList'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import AnimatedBars from '@/icons/AnimatedBars'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'
import { useRef } from 'react'

export interface LibraryTrackEntry {
  trackId: number
  title: string
  artist: string
  album: string
  duration: number
}

export const toLibraryTrackEntry = (track: UserTrack): LibraryTrackEntry => ({
  trackId: track.tid,
  title: track.title || track.filename,
  artist: track.artist ?? '',
  album: track.album ?? '',
  duration: track.duration,
})

interface Props {
  readonly totalTracks: number
  readonly tracks: readonly (LibraryTrackEntry | null)[]
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
  readonly onReachUnloadedTrack: (trackIndex: number) => void
  readonly loadMoreTracks: (
    intervals: readonly { start: number; end: number }[],
    signal: AbortSignal,
  ) => Promise<void>
}

export const LibraryTracksList: React.FC<Props> = ({
  totalTracks,
  tracks,
  onDeleteTracks,
  onReachUnloadedTrack,
  loadMoreTracks,
}) => {
  const contextMenu = useContextMenu()
  const contextMenuRef = useRef(null)

  const handleTracksListMenu = (
    selectedTracks: readonly LibraryTrackEntry[],
    event: React.MouseEvent<HTMLElement>,
  ) => {
    if (selectedTracks.length === 0) {
      return
    }

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
        totalTracks={totalTracks}
        tracks={tracks}
        currentTrack={null}
        onTracksListMenu={handleTracksListMenu}
        contextMenuRef={contextMenuRef}
        onReachUnloadedTrack={onReachUnloadedTrack}
        loadMoreTracks={loadMoreTracks}
      />
    </section>
  )
}
