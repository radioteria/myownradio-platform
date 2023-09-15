import { useCallback, useState } from 'react'
import { useTaskQueue } from '@/hooks/useTaskQueue'

interface FetchItemsResult<Item> {
  readonly items: Item[]
  readonly totalCount: number
}

interface RangeRequest {
  readonly offset: number
  readonly limit: number
}

interface VirtualListOptions<Item> {
  readonly initialTotalCount: number
  readonly initialItems: readonly Item[]
  readonly onFetchMoreItems: (
    req: RangeRequest,
    signal: AbortSignal,
  ) => Promise<FetchItemsResult<Item>>
  readonly onFetchError: (error: Error, req: RangeRequest) => void
}

const initItems = <Item extends NonNullable<unknown>>(
  initialTotalCount: number,
  initialItems: readonly Item[],
) => {
  const emptyItems = new Array<Item | null>(initialTotalCount).fill(null)
  emptyItems.splice(0, initialItems.length, ...initialItems)

  return emptyItems
}

export const useVirtualList = <Item extends NonNullable<unknown>>(
  opts: VirtualListOptions<Item>,
) => {
  const [items, setItems] = useState<readonly (Item | null)[]>(() =>
    initItems(opts.initialTotalCount, opts.initialItems),
  )

  const updateRangeOfItems = useCallback(
    (offset: number, items: readonly Item[], totalCount: number) => {
      setItems((prevItems) => {
        let newItems = [...prevItems]
        newItems.splice(offset, items.length, ...items)

        if (totalCount > newItems.length) {
          newItems.push(...new Array<null>(totalCount - newItems.length).fill(null))
        } else if (totalCount < newItems.length) {
          newItems.splice(totalCount)
        }

        return newItems
      })
    },
    [],
  )

  const { addTask } = useTaskQueue<RangeRequest>(
    useCallback(
      async (req, signal) => {
        const result = await opts.onFetchMoreItems(req, signal)

        updateRangeOfItems(req.offset, result.items, result.totalCount)
      },
      [opts, updateRangeOfItems],
    ),
  )

  const requestMoreItems = useCallback(
    (offset: number, limit: number) => {
      addTask({ offset, limit })
    },
    [addTask],
  )

  const addItemsToTop = useCallback((items: readonly Item[]) => {
    setItems((prevItems) => [...items, ...prevItems])
  }, [])

  const addItemsToBottom = useCallback((items: readonly Item[]) => {
    setItems((prevItems) => [...prevItems, ...items])
  }, [])

  const filterItems = useCallback((pred: (item: Item | null, index: number) => boolean) => {
    setItems((prevItems) => prevItems.filter(pred))
  }, [])

  return { requestMoreItems, items, addItemsToTop, addItemsToBottom, filterItems }
}
