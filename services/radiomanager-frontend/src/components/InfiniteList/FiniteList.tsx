import { useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { remove } from '@/utils/arrays'
import { ViewportReach } from './ViewportReach'
import { chunkItems } from './fns'

interface Props<Item extends NonNullable<unknown>> {
  readonly items: readonly (Item | null)[]
  readonly getItemKey: (item: Item | null, index: number) => React.Key
  readonly itemsBuffer?: number

  readonly renderSkeleton: (index: number) => React.ReactNode
  readonly renderItem: (item: Item, index: number) => React.ReactNode

  readonly loadMoreItems: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

const debug = makeDebug(FiniteList.name)

const ITEMS_PER_CHUNK = 25

export function FiniteList<Item extends NonNullable<unknown>>({
  items,
  renderSkeleton,
  renderItem,
  getItemKey,
  loadMoreItems,
}: Props<Item>) {
  const abortControllerRefs = useRef<AbortController[]>([])

  const handleOnReach = (index: number) => {
    const start = index
    const end = index + ITEMS_PER_CHUNK
    debug('Reach %dth not yet loaded element. Range to load: %d..%d', index, start, end)

    const abortController = new AbortController()
    abortControllerRefs.current.push(abortController)

    loadMoreItems(start, end, abortController.signal).finally(() => {
      remove(abortControllerRefs.current, abortController)
    })
  }

  useEffect(() => {
    const current = abortControllerRefs.current

    return () => {
      for (const controller of current) {
        controller.abort()
      }
    }
  }, [])

  return (
    <ul>
      {chunkItems(items, ITEMS_PER_CHUNK).map((itemsInChunk, chunkIndex) => {
        const indexOffset = itemsInChunk.items[0][1]

        const itemsChunkElement = itemsInChunk.items.map(([item, itemIndex]) => (
          <li key={getItemKey(item, itemIndex)}>
            {item === null ? renderSkeleton(itemIndex) : renderItem(item, itemIndex)}
          </li>
        ))

        return itemsInChunk.hasNull ? (
          <ViewportReach key={indexOffset} onReach={handleOnReach.bind(undefined, indexOffset)}>
            {itemsChunkElement}
          </ViewportReach>
        ) : (
          itemsChunkElement
        )
      })}
    </ul>
  )
}
