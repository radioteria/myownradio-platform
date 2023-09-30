import { useRef } from 'react'
import { UserTrack } from '@/api'
import { TrackList } from '../shared/TrackList'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'

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
  readonly tracks: readonly (LibraryTrackEntry | null)[]
  readonly onDeleteTracks: (trackIds: readonly number[]) => void
  readonly loadMoreTracks: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

export const LibraryTracksList: React.FC<Props> = ({ tracks, onDeleteTracks, loadMoreTracks }) => {
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
      <TrackList
        trackItems={tracks}
        currentTrack={null}
        onTrackListMenu={handleTracksListMenu}
        contextMenuRef={contextMenuRef}
        loadMoreTrackItems={loadMoreTracks}
      />
    </section>
  )
}
