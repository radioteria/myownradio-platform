import cn from 'classnames'
import React from 'react'
import { GrowBar } from './icons/GrowBar'
import { ThreeDots } from './icons/ThreeDots'
import { Duration } from '@/components/shared/Duration/Duration'
import { CurrentTrack, TrackItem } from './types'

interface Props {
  track: TrackItem
  currentTrack: CurrentTrack | null
  index: number

  isSelected: boolean
  isMainSelected: boolean
  onSelect: (event: React.MouseEvent<HTMLElement>) => void

  onThreeDotsClick: (event: React.MouseEvent<HTMLElement>) => void
}

export const TrackListItem = React.forwardRef<HTMLDivElement, Props>(
  ({ track, currentTrack, index, isSelected, isMainSelected, onSelect, onThreeDotsClick }, ref) => {
    const isCurrentTrack = currentTrack?.index === index

    const handleClick = (event: React.MouseEvent<HTMLElement>) => {
      event.preventDefault()

      onSelect(event)
    }

    return (
      <div
        ref={ref}
        key={track.trackId}
        className={cn([
          'flex items-center border-gray-800 h-12 relative cursor-pointer select-none',
          { 'bg-morblue-600 text-gray-300': isSelected },
        ])}
        onClick={handleClick}
      >
        <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right">
          {isCurrentTrack ? <GrowBar size={12} /> : <>{index + 1}</>}
        </div>
        <div className="p-2 w-[50%] z-10 min-w-0">
          <div className={'truncate'}>{track.title}</div>
        </div>
        <div
          className={cn([
            'p-2 pr-4 w-10 flex-shrink-0 text-right z-10 cursor-pointer',
            { 'opacity-0': !isMainSelected },
            { 'opacity-100': isMainSelected },
          ])}
        >
          <button onClick={onThreeDotsClick}>
            <ThreeDots size={14} />
          </button>
        </div>
        <div className="p-2 w-20 flex-shrink-0 text-right z-10">
          <Duration millis={track.duration} />
        </div>
        <div className={'p-2 w-[35%] flex-shrink-0 z-10'}>
          <div className={'truncate'}> {track.artist}</div>
        </div>
        <div className="p-2 w-[35%] z-10 truncate hidden xl:block">{track.album}</div>
      </div>
    )
  },
)

TrackListItem.displayName = 'TrackListItem'
