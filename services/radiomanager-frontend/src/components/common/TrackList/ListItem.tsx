import cn from 'classnames'
import React, { useEffect, useRef } from 'react'
import { AnimatedBars } from '@/icons/AnimatedBars'
import { ThreeDots } from '@/icons/ThreeDots'
import { Duration } from '@/components/Duration/Duration'
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

export const ListItem: React.FC<Props> = ({
  track,
  currentTrack,
  index,
  isSelected,
  isMainSelected,
  onSelect,
  onThreeDotsClick,
}) => {
  const itemRef = useRef<HTMLLIElement | null>(null)

  const isCurrentTrack = currentTrack?.index === index

  const handleClick = (event: React.MouseEvent<HTMLElement>) => {
    event.preventDefault()

    onSelect(event)
  }

  // useEffect(() => {
  //   if (isCurrentTrack) {
  //     itemRef.current?.scrollIntoView()
  //   }
  // }, [isCurrentTrack])

  return (
    <li
      ref={itemRef}
      key={track.trackId}
      className={cn([
        'flex items-center border-gray-800 h-12 relative cursor-pointer select-none',
        { 'bg-morblue-600 text-gray-300': isSelected },
      ])}
      onClick={handleClick}
    >
      <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right text-sm">
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
          { 'opacity-0': !isMainSelected },
          { 'opacity-100': isMainSelected },
        ])}
      >
        <button onClick={onThreeDotsClick}>
          <ThreeDots size={14} />
        </button>
      </div>
    </li>
  )
}
