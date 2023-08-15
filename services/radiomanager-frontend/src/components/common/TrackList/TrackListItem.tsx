import { CurrentTrack, TrackItem } from './types'
import cn from 'classnames'
import { ProgressOverlay } from '@/components/ChannelTracksList/ProgressOverlay'
import AnimatedBars from '@/icons/AnimatedBars'
import { Duration } from '@/components/Duration/Duration'
import { ThreeDots } from '@/icons/ThreeDots'
import { useState } from 'react'

interface Props {
  track: TrackItem
  currentTrack: CurrentTrack | null
  index: number
}

export const TrackListItem: React.FC<Props> = ({ track, currentTrack, index }) => {
  const isCurrentTrack = currentTrack?.index === index
  const [isDropdownVisible, setDropdownVisible] = useState(false)

  return (
    <>
      <li
        key={track.trackId}
        className={cn([
          'flex items-center border-gray-800 h-12 relative cursor-pointer',
          { 'bg-slate-600 text-gray-300': isCurrentTrack },
          { 'hover:bg-gray-300': !isCurrentTrack },
          'group',
        ])}
      >
        {isCurrentTrack && currentTrack && (
          <div className={cn('h-full w-full bg-slate-800 absolute')}>
            <ProgressOverlay position={currentTrack.position} duration={currentTrack.duration} />
          </div>
        )}
        <div className="p-2 pl-4 w-12 flex-shrink-0 z-10 text-right">
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
        <div
          className={cn([
            'p-2 pr-4 w-10 flex-shrink-0 text-right z-10 cursor-pointer',
            'opacity-0 group-hover:opacity-100 transition-[opacity]',
          ])}
        >
          <ThreeDots size={14} />
        </div>
      </li>
    </>
  )
}
