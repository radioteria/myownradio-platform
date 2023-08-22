import { UserTrack } from '@/api/api.types'
import { TrackList } from '@/components/common/TrackList'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import AnimatedBars from '@/icons/AnimatedBars'

interface LibraryTrackEntry {
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
  readonly tracks: readonly LibraryTrackEntry[]
  readonly tracksCount: number
  readonly canInfinitelyScroll: boolean
  readonly onInfiniteScroll: () => void
}

export const LibraryTracksList: React.FC<Props> = ({
  tracks,
  tracksCount,
  canInfinitelyScroll,
  onInfiniteScroll,
}) => {
  return (
    <section className={'h-full'}>
      <TrackList tracks={tracks} currentTrack={null} />
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
