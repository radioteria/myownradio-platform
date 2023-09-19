import { MutableRefObject, useRef } from 'react'
import { TrackItem, CurrentTrack } from './types'
import { ListItem } from './ListItem'
import { isModifierKeyPressed } from './helpers'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import { ListItemSkeleton } from '@/components/common/TrackList/ListItemSkeleton'
import { FiniteList } from '@/components/InfiniteList'

import type { ListItem as SelectorListItem } from '@/hooks/useListItemSelector'

interface Props<Item extends TrackItem> {
  readonly totalTracks: number
  readonly tracks: readonly (Item | null)[]
  readonly currentTrack: CurrentTrack | null
  readonly onTracksListMenu: (
    selectedTracks: readonly Item[],
    event: React.MouseEvent<HTMLElement>,
  ) => void
  readonly contextMenuRef: MutableRefObject<null>
  readonly onReachUnloadedTrack: (index: number) => void
  readonly loadMoreTracks: (
    intervals: readonly { start: number; end: number }[],
    signal: AbortSignal,
  ) => Promise<void>
}

export function TracksList<Item extends TrackItem>({
  totalTracks,
  tracks,
  currentTrack,
  onTracksListMenu,
  contextMenuRef,
  onReachUnloadedTrack,
  loadMoreTracks,
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
    const selectedTracks = tracks.filter(
      (item, index): item is Item => item !== null && index === itemIndex,
    )
    onTracksListMenu(selectedTracks, event)
  }

  const handleClickOutside = () => {
    selector.reset()
  }

  useClickOutside(listRef, handleClickOutside)

  const handleContextMenu = (event: React.MouseEvent<HTMLElement>) => {
    event.preventDefault()
    const selectedTracks = selector.listItems
      .filter((item): item is SelectorListItem<Item> => item !== null)
      .filter(({ isSelected, item }) => isSelected)
      .map(({ item }) => item)
    onTracksListMenu(selectedTracks, event)
  }

  return (
    <div ref={listRef} onContextMenu={handleContextMenu}>
      <div ref={contextMenuRef} />

      <div className={'py-4'}>
        <FiniteList
          items={selector.listItems}
          getItemKey={(_, index) => index}
          renderSkeleton={() => <ListItemSkeleton />}
          renderItem={(item, itemIndex) => (
            <ListItem
              key={itemIndex}
              track={item.item}
              currentTrack={currentTrack}
              index={itemIndex}
              isSelected={item.isSelected}
              isMainSelected={selector.cursor === itemIndex}
              onSelect={(event) => handleSelectItem(itemIndex, event)}
              onThreeDotsClick={(event) => handleTreeDotsClick(itemIndex, event)}
            />
          )}
          loadMoreItems={loadMoreTracks}
        />
      </div>
    </div>
  )
}
