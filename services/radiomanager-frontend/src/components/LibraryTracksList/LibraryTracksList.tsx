import { UserTrack } from '@/api/api.types'
import { TrackList } from '@/components/common/TrackList'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import AnimatedBars from '@/icons/AnimatedBars'
import { TrackItem } from '@/components/common/TrackList/types'

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
  const handleTracksListMenu = (selectedTracks: readonly TrackItem[]) => {
    alert(`selected ${selectedTracks.length} track(s)`)
  }

  return (
    <section className={'h-full'}>
      <TrackList tracks={tracks} currentTrack={null} onTracksListMenu={handleTracksListMenu} />
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
