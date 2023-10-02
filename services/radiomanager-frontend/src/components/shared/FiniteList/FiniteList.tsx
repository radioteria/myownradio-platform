import { useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { remove } from '@/utils/arrays'
import { ViewportReach } from './ViewportReach'
import { chunkItems } from './fns'
import { ClientServer, useClientServer } from '@/hooks/useClientServer'

interface Props<Item extends NonNullable<unknown>> {
  readonly listItems: readonly (Item | null)[]
  readonly getListItemKey: (item: Item | null, index: number) => React.Key
  readonly serverRenderedListItemsLimit: number
  readonly listItemsPerRequestMax: number

  readonly renderListItemSkeleton: (index: number) => React.ReactNode
  readonly renderListItem: (item: Item, index: number) => React.ReactNode
  readonly renderListHeader: () => React.ReactNode

  readonly loadMoreListItems: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

const debug = makeDebug(FiniteList.name)

export function FiniteList<Item extends NonNullable<unknown>>({
  listItems,
  renderListItemSkeleton,
  renderListItem,
  renderListHeader,
  getListItemKey,
  serverRenderedListItemsLimit,
  listItemsPerRequestMax,
  loadMoreListItems,
}: Props<Item>) {
  const abortControllerRefs = useRef<AbortController[]>([])
  const clientServer = useClientServer()

  const handleOnReach = (index: number) => {
    const start = index
    const end = index + listItemsPerRequestMax
    debug('Reach %dth not yet loaded element. Range to load: %d..%d', index, start, end)

    const abortController = new AbortController()
    abortControllerRefs.current.push(abortController)

    loadMoreListItems(start, end, abortController.signal).finally(() => {
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
    clientServer === ClientServer.Server
      ? listItems.slice(0, serverRenderedListItemsLimit)
      : listItems

  return (
    <ul>
      {renderListHeader()}

      {chunkItems(itemsToRender, listItemsPerRequestMax).map((itemsInChunk, chunkIndex) => {
        const indexOffset = itemsInChunk.items[0][1]

        const chunkElement = itemsInChunk.items.map(([item, itemIndex]) => (
          <li key={getListItemKey(item, itemIndex)}>
            {item === null ? renderListItemSkeleton(itemIndex) : renderListItem(item, itemIndex)}
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
