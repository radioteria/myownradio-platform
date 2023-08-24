import cn from 'classnames'
import AnimatedBars from '@/icons/AnimatedBars'
import { Duration } from '@/components/Duration/Duration'
import { ThreeDots } from '@/icons/ThreeDots'
import { CurrentTrack, TrackItem } from './types'

interface Props {
  track: TrackItem
  currentTrack: CurrentTrack | null
  index: number
  onRemoveFromLibrary: () => void
  onRemoveFromChannel?: () => void

  isSelected: boolean
  onSelect: () => void
  onThreeDotsClick: () => void
}

export const TrackListItem: React.FC<Props> = ({
  track,
  currentTrack,
  index,
  isSelected,
  onSelect,
  onThreeDotsClick,
}) => {
  const isCurrentTrack = currentTrack?.index === index

  return (
    <li
      key={track.trackId}
      className={cn([
        'flex items-center border-gray-800 h-12 relative cursor-pointer',
        { 'bg-morblue-600 text-gray-300': isSelected },
      ])}
      onClick={(ev) => {
        ev.preventDefault()
        onSelect()
      }}
    >
      <div className="p-2 pl-4 w-12 flex-shrink-0 z-10 text-right">
        {isCurrentTrack ? <AnimatedBars size={12} /> : <>{index + 1}</>}
      </div>
      <div className="p-2 w-full z-10 min-w-0">
        <div className={'truncate'}>{track.title}</div>
        {track.artist && <div className={'text-xs truncate'}>{track.artist}</div>}
      </div>
      {track.album && <div className="p-2 w-full z-10 truncate hidden xl:block">{track.album}</div>}
      <div className="p-2 w-20 flex-shrink-0 text-right z-10">
        <Duration millis={track.duration} />
      </div>
      <div
        className={cn([
          'p-2 pr-4 w-10 flex-shrink-0 text-right z-10 cursor-pointer',
          { 'opacity-0': !isSelected },
          { 'opacity-100': isSelected },
        ])}
      >
        <button onClick={onThreeDotsClick}>
          <ThreeDots size={14} />
        </button>
      </div>
    </li>
  )
}
