'use client'

import { TrackList } from '@/components/common/TrackList'
import { useNowPlaying } from '@/modules/NowPlaying'
import { UserChannelTrack } from '@/api/api.types'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import AnimatedBars from '@/icons/AnimatedBars'

interface ChannelTrackEntry {
  trackId: number
  title: string
  artist: string
  album: string
  duration: number
}

export const toChannelTrackEntry = (track: UserChannelTrack): ChannelTrackEntry => ({
  trackId: track.tid,
  title: track.title || track.filename,
  artist: track.artist,
  album: track.album,
  duration: track.duration,
})

interface Props {
  readonly tracks: readonly ChannelTrackEntry[]
  readonly tracksCount: number
  readonly channelId: number
  readonly canInfinitelyScroll: boolean
  readonly onInfiniteScroll: () => void
}

export const ChannelTracksList: React.FC<Props> = ({
  tracks,
  channelId,
  canInfinitelyScroll,
  onInfiniteScroll,
}) => {
  const { nowPlaying } = useNowPlaying()
  const currentTrack = nowPlaying
    ? {
        index: nowPlaying.playlistPosition - 1,
        position: nowPlaying.currentTrack.offset,
        duration: nowPlaying.currentTrack.duration,
      }
    : null

  return (
    <section className={'h-full'}>
      <TrackList tracks={tracks} currentTrack={currentTrack} />
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
