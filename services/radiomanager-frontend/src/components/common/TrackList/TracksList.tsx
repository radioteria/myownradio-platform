import { MutableRefObject, useEffect, useRef, useState } from 'react'
import { TrackItem, CurrentTrack } from './types'
import { ListItem } from './ListItem'
import { isModifierKeyPressed } from './helpers'
import { useClickOutside } from '@/hooks/useClickOutside'
import { useListItemSelector } from '@/hooks/useListItemSelector'
import { SkeletonList } from '@/components/common/TrackList/SkeletonList'
import { InfiniteScroll } from '@/components/common/InfiniteScroll/InfiniteScroll'
import { noop } from '@/utils/fun'
import { SkeletonItem } from '@/components/common/TrackList/SkeletonItem'

interface Props<Item extends TrackItem> {
  readonly totalTracks: number
  readonly topTrackOffset: number
  readonly tracks: readonly Item[]
  readonly currentTrack: CurrentTrack | null
  readonly onTracksListMenu: (
    selectedTracks: readonly Item[],
    event: React.MouseEvent<HTMLElement>,
  ) => void
  readonly contextMenuRef: MutableRefObject<null>
  readonly onScrollTop?: () => void
  readonly onScrollBottom?: () => void
}

export function TracksList<Item extends TrackItem>({
  totalTracks,
  topTrackOffset,
  tracks,
  currentTrack,
  onTracksListMenu,
  contextMenuRef,
  onScrollTop,
  onScrollBottom,
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

  const topSkeletonLength = topTrackOffset
  const bottomSkeletonLength = totalTracks - topTrackOffset - tracks.length

  const scrollRef = useRef<HTMLUListElement | null>(null)

  useEffect(() => {
    scrollRef.current?.scrollIntoView()
  }, [scrollRef])

  return (
    <div ref={listRef} onContextMenu={handleContextMenu}>
      <div ref={contextMenuRef} />
      {topSkeletonLength > 0 && (
        <>
          <SkeletonList length={topSkeletonLength - 1} />
          <InfiniteScroll key={`top-${tracks.length}`} onReach={onScrollTop ?? noop}>
            <SkeletonItem />
          </InfiniteScroll>
        </>
      )}
      <ul ref={scrollRef}>
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
      {bottomSkeletonLength > 0 && (
        <>
          <InfiniteScroll key={`bottom-${tracks.length}`} onReach={onScrollBottom ?? noop}>
            <SkeletonItem />
          </InfiniteScroll>
          <SkeletonList length={bottomSkeletonLength} />
        </>
      )}
    </div>
  )
}
