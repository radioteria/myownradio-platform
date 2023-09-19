import { useEffect, useRef, useState } from 'react'
import makeDebug from 'debug'
import { OnReachTrigger } from './OnReachTrigger'
import { numbersToExclusiveIntervals } from './helpers'
import { range } from '@/utils/iterators'

import type { Interval } from './types'

interface ListItem {}

interface LoadMoreItemsResult<Item extends NonNullable<ListItem>> {
  items: readonly Item[]
  totalCount: number
}

interface LoadRequest {
  readonly intervals: readonly Interval[]
}

interface Props<Item extends NonNullable<ListItem>> {
  readonly items: readonly (Item | null)[]
  readonly getItemKey: (item: Item | null, index: number) => React.Key

  readonly renderSkeleton: (index: number) => React.ReactNode
  readonly renderItem: (item: Item, index: number) => React.ReactNode

  readonly loadMoreItems: (intervals: readonly Interval[], signal: AbortSignal) => Promise<void>
}

const debug = makeDebug(FiniteList.name)

export function FiniteList<Item extends NonNullable<ListItem>>({
  items,
  renderSkeleton,
  renderItem,
  getItemKey,
  loadMoreItems,
}: Props<Item>) {
  const isLoadingRef = useRef(false)
  const [loadRequest, setLoadRequest] = useState<null | LoadRequest>(null)

  // Trigger "loadMoreItems"
  const handleOnReach = (index: number) => {
    if (isLoadingRef.current) return

    const start = Math.max(0, index - 25)
    const end = Math.min(items.length, index + 25)
    debug('Reach %dth not yet loaded element. Range to load: %d..%d', index, start, end)

    const rangeToLoad = range(start, end).filter((index) => items[index] === null)
    const rangeIntervals = numbersToExclusiveIntervals(rangeToLoad)
    const rangeIntervalsStr = rangeIntervals.map(({ start, end }) => `${start}..${end}`).join(', ')
    debug('Refined range intervals to load: %s', rangeIntervalsStr)

    // Load more data
    setLoadRequest({ intervals: rangeIntervals })
    isLoadingRef.current = true
  }

  // Handle "loadMoreItems"
  useEffect(() => {
    if (!loadRequest) return

    const abortController = new AbortController()

    loadMoreItems(loadRequest.intervals, abortController.signal).finally(() => {
      isLoadingRef.current = false
      setLoadRequest(null)
    })

    return () => {
      abortController.abort()
    }
  }, [loadRequest, loadMoreItems])

  return (
    <ul>
      {items.map((item, index) => (
        <li key={getItemKey(item, index)}>
          {item === null ? (
            <OnReachTrigger onReach={handleOnReach.bind(undefined, index)}>
              {renderSkeleton(index)}
            </OnReachTrigger>
          ) : (
            renderItem(item, index)
          )}
        </li>
      ))}
    </ul>
  )
}
