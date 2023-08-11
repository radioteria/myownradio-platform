'use client'

import cn from 'classnames'
import { UserChannelTrack } from '@/api/api.types'
import { Duration } from '@/components/Duration/Duration'
import { useNowPlaying } from '@/hooks/useNowPlaying'
import { ProgressOverlay } from '@/components/ChannelTracksList/ProgressOverlay'

interface Props {
  tracks: readonly UserChannelTrack[]
  tracksCount: number
  channelId: number
}

export const ChannelTracksList: React.FC<Props> = ({ tracks, channelId }) => {
  const nowPlaying = useNowPlaying(channelId)

  return (
    <section>
      <ul className={'mt-2'}>
        <li className="flex text-gray-600 h-12">
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-full">Title</div>
          <div className="p-2 w-full hidden xl:block">Album</div>
          <div className="p-2 w-20 flex-shrink-0 text-right">⏱</div>
        </li>

        {tracks.map((track, index) => {
          const isCurrentTrack = nowPlaying?.playlistPosition === index + 1

          return (
            <li
              key={track.tid}
              className={cn('flex items-center border-gray-800 relative h-12', {
                'bg-slate-600 text-gray-300': isCurrentTrack,
              })}
            >
              {isCurrentTrack && nowPlaying && (
                <div className={cn('h-full w-full bg-slate-800 absolute')}>
                  <ProgressOverlay
                    position={nowPlaying.currentTrack.offset}
                    duration={nowPlaying.currentTrack.duration}
                  />
                </div>
              )}
              <div className="p-2 w-8 flex-shrink-0 z-10">▶️</div>
              <div className="p-2 w-full z-10">
                <div>{track.title || track.filename}</div>
                {track.artist && <div className={'text-xs'}>{track.artist}</div>}
              </div>
              <div className="p-2 w-full z-10 hidden xl:block">{track.album}</div>
              <div className="p-2 w-20 flex-shrink-0 text-right z-10">
                <Duration millis={track.duration} />
              </div>
            </li>
          )
        })}
      </ul>
    </section>
  )
}
