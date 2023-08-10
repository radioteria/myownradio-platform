'use client'

import cn from 'classnames'
import { UserChannelTrack } from '@/api/api.types'
import { Duration } from '@/components/Duration/Duration'
import { useNowPlaying } from '@/hooks/useNowPlaying'

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
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-full">Title</div>
          <div className="p-2 w-full">Artist</div>
          <div className="p-2 w-full">Album</div>
          <div className="p-2 w-20 flex-shrink-0 text-right">⏱</div>
        </li>

        {tracks.map((track, index) => (
          <li
            key={track.tid}
            className={cn('flex border-gray-800 bg', {
              'bg-slate-800 text-gray-300': nowPlaying?.playlistPosition === index + 1,
            })}
          >
            <div className="p-3 w-8 flex-shrink-0">▶️</div>
            <div className="p-3 w-8 flex-shrink-0">❤️</div>
            <div className="p-3 w-full">{track.title || track.filename}</div>
            <div className="p-3 w-full">{track.artist}</div>
            <div className="p-3 w-full">{track.album}</div>
            <div className="p-3 w-20 flex-shrink-0 text-right">
              <Duration millis={track.duration} />
            </div>
          </li>
        ))}
      </ul>
    </section>
  )
}
