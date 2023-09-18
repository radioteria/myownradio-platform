import { MutableRefObject, useRef } from 'react'
import { TrackItem, CurrentTrack } from './types'
import { ListItem } from './ListItem'
import { isModifierKeyPressed } from './helpers'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import { ListItemSkeleton } from '@/components/common/TrackList/ListItemSkeleton'
import { range } from '@/utils/iterators'
import { ClientSide } from '@/components/common/ClientSide'
import { FiniteList } from '@/components/InfiniteList'

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
    startIndex: number,
    endIndex: number,
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
      .filter(({ isSelected, item }) => isSelected)
      .map(({ item }) => item)
      .filter((item): item is Item => item !== null)
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
          renderItem={(item, itemIndex) =>
            item.item === null ? (
              <ListItemSkeleton />
            ) : (
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
            )
          }
          loadMoreItems={loadMoreTracks}
        />
      </div>

      {/*<ul className={'py-4'}>*/}
      {/*  {selector.listItems.map(({ item, isSelected }, itemIndex) => {*/}
      {/*    if (!item) {*/}
      {/*      return (*/}
      {/*        <ListItemSkeleton*/}
      {/*          key={itemIndex}*/}
      {/*          onReach={onReachUnloadedTrack.bind(undefined, itemIndex)}*/}
      {/*        />*/}
      {/*      )*/}
      {/*    }*/}

      {/*    return (*/}
      {/*      <ListItem*/}
      {/*        key={itemIndex}*/}
      {/*        track={item}*/}
      {/*        currentTrack={currentTrack}*/}
      {/*        index={itemIndex}*/}
      {/*        isSelected={isSelected}*/}
      {/*        isMainSelected={selector.cursor === itemIndex}*/}
      {/*        onSelect={(event) => handleSelectItem(itemIndex, event)}*/}
      {/*        onThreeDotsClick={(event) => handleTreeDotsClick(itemIndex, event)}*/}
      {/*      />*/}
      {/*    )*/}
      {/*  })}*/}
      {/*</ul>*/}
    </div>
  )
}
