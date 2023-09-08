import { MutableRefObject, useRef } from 'react'
import { TrackItem, CurrentTrack } from './types'
import { ListItem } from './ListItem'
import { isModifierKeyPressed } from './helpers'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'

interface Props<Item extends TrackItem> {
  readonly tracks: readonly Item[]
  readonly currentTrack: CurrentTrack | null
  readonly onTracksListMenu: (
    selectedTracks: readonly Item[],
    event: React.MouseEvent<HTMLElement>,
  ) => void
  readonly contextMenuRef: MutableRefObject<null>
}

export function TracksList<Item extends TrackItem>({
  tracks,
  currentTrack,
  onTracksListMenu,
  contextMenuRef,
}: Props<Item>) {
  const listRef = useRef(null)
  const selector = useListItemSelector(tracks)

  const handleSelectItem = (itemIndex: number, event: React.MouseEvent<HTMLElement>) => {
    event.preventDefault()

    isModifierKeyPressed(event)
      ? selector.select(itemIndex)
      : event.shiftKey
      ? selector.selectTo(itemIndex)
      : selector.selectOnly(itemIndex)
  }

  const handleTreeDotsClick = (itemIndex: number, event: React.MouseEvent<HTMLElement>) => {
    selector.selectOnly(itemIndex)
    const selectedTracks = tracks.filter((_, index) => index === itemIndex)
    onTracksListMenu(selectedTracks, event)
  }

  const handleClickOutside = () => {
    selector.reset()
  }

  useClickOutside(listRef, handleClickOutside)

  const handleContextMenu = (event: React.MouseEvent<HTMLElement>) => {
    event.preventDefault()
    const selectedTracks = selector.listItems
      .filter(({ isSelected }) => isSelected)
      .map(({ item }) => item)
    onTracksListMenu(selectedTracks, event)
  }

  return (
    <div ref={listRef} onContextMenu={handleContextMenu}>
      <div ref={contextMenuRef} />
      <ul className={''}>
        {selector.listItems.map((listItem, index) => {
          return (
            <ListItem
              key={index}
              track={listItem.item}
              currentTrack={currentTrack}
              index={index}
              isSelected={listItem.isSelected}
              isMainSelected={selector.cursor === index}
              onSelect={(event) => handleSelectItem(index, event)}
              onThreeDotsClick={(event) => handleTreeDotsClick(index, event)}
            />
          )
        })}
      </ul>
    </div>
  )
}
