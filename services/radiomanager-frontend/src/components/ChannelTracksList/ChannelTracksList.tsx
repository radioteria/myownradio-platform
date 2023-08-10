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
      <ul className={'mt-8'}>
        <li className="flex text-gray-600">
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-full">Title</div>
          <div className="p-2 w-full">Artist</div>
          <div className="p-2 w-full">Album</div>
          <div className="p-2 w-20 flex-shrink-0 text-right">⏱</div>
        </li>

        {tracks.map((track, index) => {
          const isCurrentTrack = nowPlaying?.playlistPosition === index + 1
          const currentTrack = nowPlaying?.currentTrack

          return (
            <li
              key={track.tid}
              className={cn('flex border-gray-800 relative', {
                'bg-slate-600 text-gray-300': isCurrentTrack,
              })}
            >
              {isCurrentTrack && (
                <div className={cn('h-full w-full bg-slate-800 absolute')}>
                  <ProgressOverlay
                    position={currentTrack?.offset ?? 0}
                    duration={currentTrack?.duration ?? 0}
                    timestamp={nowPlaying?.time ?? 0}
                  />
                </div>
              )}
              <div className="p-3 w-8 flex-shrink-0 z-10">▶️</div>
              <div className="p-3 w-full z-10">{track.title || track.filename}</div>
              <div className="p-3 w-full z-10">{track.artist}</div>
              <div className="p-3 w-full z-10">{track.album}</div>
              <div className="p-3 w-20 flex-shrink-0 text-right z-10">
                <Duration millis={track.duration} />
              </div>
            </li>
          )
        })}
      </ul>
    </section>
  )
}
