import { useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { remove } from '@/utils/arrays'
import { ViewportReach } from './ViewportReach'
import { chunkItems } from './fns'
import { ClientServer, useClientServer } from '@/hooks/useClientServer'

interface Props<Item extends NonNullable<unknown>> {
  readonly items: readonly (Item | null)[]
  readonly getItemKey: (item: Item | null, index: number) => React.Key
  readonly serverItemsLimit: number
  readonly loadRequestItemsMax: number

  readonly renderSkeleton: (index: number) => React.ReactNode
  readonly renderItem: (item: Item, index: number) => React.ReactNode

  readonly loadMoreItems: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

const debug = makeDebug(FiniteList.name)

export function FiniteList<Item extends NonNullable<unknown>>({
  items,
  renderSkeleton,
  renderItem,
  getItemKey,
  serverItemsLimit,
  loadRequestItemsMax,
  loadMoreItems,
}: Props<Item>) {
  const abortControllerRefs = useRef<AbortController[]>([])
  const clientServer = useClientServer()

  const handleOnReach = (index: number) => {
    const start = index
    const end = index + loadRequestItemsMax
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

  const itemsToRender =
    clientServer === ClientServer.Server ? items.slice(0, serverItemsLimit) : items

  return (
    <ul>
      {chunkItems(itemsToRender, loadRequestItemsMax).map((itemsInChunk, chunkIndex) => {
        const indexOffset = itemsInChunk.items[0][1]

        const chunkElement = itemsInChunk.items.map(([item, itemIndex]) => (
          <li key={getItemKey(item, itemIndex)}>
            {item === null ? renderSkeleton(itemIndex) : renderItem(item, itemIndex)}
          </li>
        ))

        return itemsInChunk.hasNull ? (
          <ViewportReach key={indexOffset} onReach={handleOnReach.bind(undefined, indexOffset)}>
            {chunkElement}
          </ViewportReach>
        ) : (
          chunkElement
        )
      })}
    </ul>
  )
}
