import { MutableRefObject, useRef } from 'react'
import { TrackListItem } from './TrackListItem'
import { TrackListItemSkeleton } from './TrackListItemSkeleton'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import { FiniteList } from '../FiniteList'
import { isModifierKeyPressed } from './helpers'
import { INITIAL_AUDIO_TRACKS_CHUNK_SIZE, NEXT_AUDIO_TRACKS_CHUNKS_SIZE } from '@/constants'

import type { TrackItem, CurrentTrack } from './types'
import type { ListItem as SelectorListItem } from '@/hooks/useListItemSelector'

interface Props<Item extends TrackItem> {
  readonly trackItems: readonly (Item | null)[]
  readonly currentTrack: CurrentTrack | null
  readonly onTrackListMenu: (
    selectedTrackItems: readonly Item[],
    event: React.MouseEvent<HTMLElement>,
  ) => void
  readonly contextMenuRef: MutableRefObject<null>
  readonly loadMoreTrackItems: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

export function TrackList<Item extends TrackItem>({
  trackItems,
  currentTrack,
  onTrackListMenu,
  contextMenuRef,
  loadMoreTrackItems,
}: Props<Item>) {
  const listRef = useRef(null)
  const selector = useListItemSelector(trackItems)

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
    const selectedTracks = trackItems.filter(
      (item, index): item is Item => item !== null && index === itemIndex,
    )
    onTrackListMenu(selectedTracks, event)
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
    onTrackListMenu(selectedTracks, event)
  }

  return (
    <div ref={listRef} onContextMenu={handleContextMenu}>
      <div ref={contextMenuRef} />

      <div className={'py-4'}>
        <FiniteList
          listItems={selector.listItems}
          getListItemKey={(_, index) => index}
          renderListItemSkeleton={() => <TrackListItemSkeleton />}
          renderListItem={(item, itemIndex) => (
            <TrackListItem
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
          loadMoreListItems={loadMoreTrackItems}
          serverRenderedListItemsLimit={INITIAL_AUDIO_TRACKS_CHUNK_SIZE}
          listItemsPerRequestMax={NEXT_AUDIO_TRACKS_CHUNKS_SIZE}
        />
      </div>
    </div>
  )
}
