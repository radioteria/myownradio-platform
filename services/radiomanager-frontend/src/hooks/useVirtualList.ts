import { useCallback, useEffect, useMemo, useState } from 'react'
import PQueue from 'p-queue'
import { range } from '@/utils/iterators'

interface FetchItemsResult<Item> {
  readonly items: Item[]
  readonly totalCount: number
}

interface RequestedRange {
  readonly offset: number
  readonly limit: number
}

interface VirtualListOptions<Item> {
  readonly initialTotalCount: number
  readonly initialItems: readonly Item[]
  readonly fetchItems: (
    offset: number,
    limit: number,
    signal: AbortSignal,
  ) => Promise<FetchItemsResult<Item>>
  readonly onFetchError: (error: Error, offset: number, limit: number) => void
}

const fetchPromiseQueue = new PQueue({ concurrency: 1 })

export const useVirtualList = <Item extends NonNullable<unknown>>(
  opts: VirtualListOptions<Item>,
) => {
  const [totalCount, setTotalCount] = useState(opts.initialTotalCount)
  const [items, setItems] = useState<Record<number, Item>>({})
  const [requestedRanges, setRequestedRanges] = useState<readonly RequestedRange[]>([])

  const itemsList = useMemo(() => {
    let itemsList: (Item | null)[] = []

    for (let i = 0; i < totalCount; i += 1) {
      itemsList.push(items[i] ?? null)
    }

    return itemsList
  }, [totalCount, items])

  const addItems = useCallback((offset: number, items: readonly Item[]) => {
    setItems((oldItems) => {
      let newItems = { ...oldItems }

      for (let i = 0; i < items.length; i += 1) {
        newItems[i + offset] = items[i]
      }

      return newItems
    })
  }, [])

  useEffect(() => {
    if (requestedRanges.length === 0) return

    const [{ offset, limit }, ...restRequestedRanges] = requestedRanges

    const abortController = new AbortController()

    fetchPromiseQueue
      .add(async () => {
        const data = await opts.fetchItems(offset, limit, abortController.signal)
        addItems(offset, data.items)
        setTotalCount(data.totalCount)
      })
      .catch((error) => opts.onFetchError(error, offset, limit))
      .finally(() => {
        setRequestedRanges(restRequestedRanges)
      })

    return () => {
      abortController.abort()
    }
  }, [requestedRanges, addItems, opts])

  const requestItemsRange = useCallback((offset: number, limit: number) => {
    setRequestedRanges((ranges) => [...ranges, { offset, limit }])
  }, [])

  return {
    itemsList,
    requestItemsRange,
  }
}
