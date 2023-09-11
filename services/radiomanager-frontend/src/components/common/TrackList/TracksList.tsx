import { MutableRefObject, useRef } from 'react'
import { TrackItem, CurrentTrack } from './types'
import { ListItem } from './ListItem'
import { isModifierKeyPressed } from './helpers'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import { ListItemSkeleton } from '@/components/common/TrackList/ListItemSkeleton'
import { range } from '@/utils/iterators'

interface Props<Item extends TrackItem> {
  readonly totalTracks: number
  readonly tracks: readonly Item[]
  readonly currentTrack: CurrentTrack | null
  readonly onTracksListMenu: (
    selectedTracks: readonly Item[],
    event: React.MouseEvent<HTMLElement>,
  ) => void
  readonly contextMenuRef: MutableRefObject<null>
}

export function TracksList<Item extends TrackItem>({
  totalTracks,
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
      .filter(({ isSelected, item }) => isSelected)
      .map(({ item }) => item)
    onTracksListMenu(selectedTracks, event)
  }

  return (
    <div ref={listRef} onContextMenu={handleContextMenu}>
      <div ref={contextMenuRef} />

      <ul className={'py-4'}>
        {selector.listItems.map(({ item, isSelected }, itemIndex) => {
          return (
            <ListItem
              key={itemIndex}
              track={item}
              currentTrack={currentTrack}
              index={itemIndex}
              isSelected={isSelected}
              isMainSelected={selector.cursor === itemIndex}
              onSelect={(event) => handleSelectItem(itemIndex, event)}
              onThreeDotsClick={(event) => handleTreeDotsClick(itemIndex, event)}
            />
          )
        })}

        {[...range(selector.listItems.length, totalTracks)].map((n) => (
          <ListItemSkeleton key={n} />
        ))}
      </ul>
    </div>
  )
}
