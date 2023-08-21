import cn from 'classnames'
import { useRef } from 'react'
import { ProgressBar } from '@/components/entries/ChannelPage/ChannelControls/ProgressBar'
import AnimatedBars from '@/icons/AnimatedBars'
import { Duration } from '@/components/Duration/Duration'
import { ThreeDots } from '@/icons/ThreeDots'
import { MenuItemType, useContextMenu } from '@/modules/ContextMenu'
import { CurrentTrack, TrackItem } from './types'

interface Props {
  track: TrackItem
  currentTrack: CurrentTrack | null
  index: number
  onRemoveFromLibrary: () => void
  onRemoveFromChannel?: () => void

  isSelected: boolean
  onSelect: () => void
}

export const TrackListItem: React.FC<Props> = ({
  track,
  currentTrack,
  index,
  isSelected,
  onSelect,
}) => {
  const isCurrentTrack = currentTrack?.index === index
  const portalRef = useRef<HTMLDivElement | null>(null)
  const contextMenu = useContextMenu()

  function showMenu(position: { x: number; y: number }) {
    contextMenu.show({
      position,
      portalElement: portalRef.current ?? undefined,
      menuItems: [
        {
          type: MenuItemType.Item,
          label: 'Remove from channel',
          onClick() {},
        },
        {
          type: MenuItemType.Item,
          label: 'Remove from library',
          onClick() {},
        },
      ],
    })
  }

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
      onContextMenu={(ev) => {
        ev.preventDefault()
        showMenu({ x: ev.clientX, y: ev.clientY })
      }}
    >
      <div ref={portalRef} />
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
        <button
          onClick={(ev) => {
            ev.preventDefault()
            showMenu({ x: ev.clientX, y: ev.clientY })
          }}
        >
          <ThreeDots size={14} />
        </button>
      </div>
    </li>
  )
}
