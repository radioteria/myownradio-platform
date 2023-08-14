import cn from 'classnames'
import { Duration } from '@/components/Duration/Duration'
import { ProgressOverlay } from '@/components/ChannelTracksList/ProgressOverlay'
import { SpeakerIcon } from '@/icons/SpeakerIcon'
import AnimatedBars from '@/icons/AnimatedBars'
import { ThreeDots } from '@/icons/ThreeDots'

interface TrackItem {
  trackId: number
  title: string
  artist: string | null
  album: string
  duration: number
}

interface CurrentTrack {
  index: number
  position: number
  duration: number
}

interface Props {
  tracks: readonly TrackItem[]
  currentTrack: CurrentTrack | null
}

export const TrackList: React.FC<Props> = ({ tracks, currentTrack }) => {
  return (
    <ul>
      <li className="flex text-gray-600 h-12">
        <div className="pl-4 pr-2 py-4 w-14 flex-shrink-0 text-right">#</div>
        <div className="px-2 py-4 w-full">Title</div>
        <div className="px-2 py-4 w-full hidden xl:block">Album</div>
        <div className="px-2 py-4 w-20 flex-shrink-0 text-right">⏱</div>
        <div className="pl-2 pr-4 py-4 w-14 flex-shrink-0 text-right" />
      </li>

      {tracks.map((track, index) => {
        const isCurrentTrack = currentTrack?.index === index

        return (
          <li
            key={track.trackId}
            className={cn('flex items-center border-gray-800 h-12 relative', {
              'bg-slate-600 text-gray-300': isCurrentTrack,
            })}
          >
            {isCurrentTrack && currentTrack && (
              <div className={cn('h-full w-full bg-slate-800 absolute')}>
                <ProgressOverlay
                  position={currentTrack.position}
                  duration={currentTrack.duration}
                />
              </div>
            )}
            <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right">
              {isCurrentTrack ? <AnimatedBars size={12} /> : <>{index + 1}</>}
            </div>
            <div className="p-2 w-full z-10 min-w-0">
              <div className={'truncate'}>{track.title}</div>
              {track.artist && <div className={'text-xs truncate'}>{track.artist}</div>}
            </div>
            {track.album && (
              <div className="p-2 w-full z-10 truncate hidden xl:block">{track.album}</div>
            )}
            <div className="p-2 w-20 flex-shrink-0 text-right z-10">
              <Duration millis={track.duration} />
            </div>
            <div className="p-2 pr-4 w-14 flex-shrink-0 text-right z-10 cursor-pointer">
              <ThreeDots size={12} />
            </div>
          </li>
        )
      })}
    </ul>
  )
}
