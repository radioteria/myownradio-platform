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
          'grid gap-4 grid-cols-playlist-item p-4',
          'border-gray-800 relative cursor-pointer select-none',
          isCurrentTrack ? 'text-gray-0 font-semibold' : 'text-morblue-100',
          { 'bg-morblue-600 text-gray-300': isSelected },
        ])}
        onClick={handleClick}
      >
        <div className="flex overflow-hidden items-center justify-end">
          {isCurrentTrack ? <GrowBar size={12} /> : <>{index + 1}</>}
        </div>
        <div className="flex overflow-hidden">
          <div className={'truncate'}>{track.title}</div>
        </div>
        <div
          className={cn([
            'flex cursor-pointer overflow-hidden items-center',
            { 'text-morblue-200': !isMainSelected },
          ])}
        >
          <button onClick={onThreeDotsClick}>
            <ThreeDots size={14} />
          </button>
        </div>
        <div className="flex overflow-hidden items-center justify-end">
          <Duration millis={track.duration} />
        </div>
        <div className="flex overflow-hidden items-center">
          <div className={'truncate'}>{track.artist || '-'}</div>
        </div>
        <div className="flex overflow-hidden items-center">
          <div className={'truncate'}>{track.album || '-'}</div>
        </div>
      </div>
    )
  },
)

TrackListItem.displayName = 'TrackListItem'
