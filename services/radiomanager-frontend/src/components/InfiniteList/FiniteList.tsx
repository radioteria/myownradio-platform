import { OnReachTrigger } from './OnReachTrigger'
import { useEffect, useState } from 'react'

interface ListItem {}

interface LoadMoreItemsResult<Item extends NonNullable<ListItem>> {
  items: readonly Item[]
  totalCount: number
}

interface LoadRequest {
  readonly startIndex: number
  readonly endIndex: number
}

interface Props<Item extends NonNullable<ListItem>> {
  readonly items: readonly (Item | null)[]
  readonly getItemKey: (item: Item | null, index: number) => React.Key

  readonly renderSkeleton: (index: number) => React.ReactNode
  readonly renderItem: (item: Item, index: number) => React.ReactNode

  readonly loadMoreItems: (
    startIndex: number,
    endIndex: number,
    signal: AbortSignal,
  ) => Promise<void>
}

export function FiniteList<Item extends NonNullable<ListItem>>({
  items,
  renderSkeleton,
  renderItem,
  getItemKey,
  loadMoreItems,
}: Props<Item>) {
  const [isLoading, setIsLoading] = useState(false)
  const [loadRequest, setLoadRequest] = useState<null | LoadRequest>(null)

  // Trigger "loadMoreItems"
  const handleOnReach = (index: number) => {
    if (isLoading) return

    if (items[index] === null) {
      console.log('reach', index)

      const startIndex = Math.max(0, index - 25)
      const endIndex = index + 25

      // Load more data
      setLoadRequest({ startIndex, endIndex })
      setIsLoading(true)
    }
  }

  // Handle "loadMoreItems"
  useEffect(() => {
    if (!loadRequest) return

    const abortController = new AbortController()

    loadMoreItems(loadRequest.startIndex, loadRequest.endIndex, abortController.signal).finally(
      () => {
        setIsLoading(false)
        setLoadRequest(null)
      },
    )

    return () => {
      abortController.abort()
    }
  }, [loadRequest, loadMoreItems])

  return (
    <ul>
      {items.map((item, index) => (
        <li key={getItemKey(item, index)}>
          <OnReachTrigger onReach={handleOnReach.bind(undefined, index)}>
            {item === null ? renderSkeleton(index) : renderItem(item, index)}
          </OnReachTrigger>
        </li>
      ))}
    </ul>
  )
}
