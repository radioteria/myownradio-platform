import { RefObject, useCallback, useEffect, useState } from 'react'
import { useHotkey } from '@/hooks/useHotkey'

export interface ListItem<I> {
  item: I
  isSelected: boolean
}

const makeListItem = <I extends NonNullable<unknown>>(item: I | null) =>
  item === null ? null : { item, isSelected: false }

const mapToListItems = <T extends NonNullable<unknown>>(items: readonly (T | null)[]) => {
  return items.map(makeListItem)
}

export const useListItemSelector = <I extends NonNullable<unknown>>(
  initialItems: readonly (I | null)[],
) => {
  const [listItems, setListItems] = useState<readonly (ListItem<I> | null)[]>(() =>
    mapToListItems(initialItems),
  )
  const [cursor, setCursor] = useState<null | number>(null)

  // Reset selection on tracks list update.
  useEffect(() => {
    setListItems((prevItems) => {
      const prevItemsMap = new Map(
        prevItems
          .filter((item): item is ListItem<I> => item !== null)
          .map((item) => [item.item, item]),
      )

      return initialItems.map((item) => {
        if (item === null) return null
        const prevItem = prevItemsMap.get(item)
        return prevItem ? { ...prevItem, item } : makeListItem(item)
      })
    })
  }, [initialItems])

  const select = (selectIndex: number) => {
    setCursor(selectIndex)
    setListItems((items) =>
      items.map((item, index) => {
        if (item === null) return null
        return selectIndex === index ? { ...item, isSelected: true } : item
      }),
    )
  }

  const toggle = (selectIndex: number) => {
    setCursor(selectIndex)
    setListItems((items) =>
      items.map((item, index) => {
        if (item === null) return null
        return selectIndex === index ? { ...item, isSelected: !item.isSelected } : item
      }),
    )
  }

  const selectOnly = (selectIndex: number) => {
    setCursor(selectIndex)
    setListItems((items) =>
      items.map((item, index) => {
        if (item === null) return null
        return { ...item, isSelected: selectIndex === index }
      }),
    )
  }

  const discard = (discardIndex: number) => {
    setCursor(discardIndex)
    setListItems((items) =>
      items.map((item, index) => {
        if (item === null) return null
        return discardIndex === index ? { ...item, isSelected: false } : item
      }),
    )
  }

  const selectTo = (endIndex: number) => {
    const startIndex = cursor ?? 0

    setListItems((items) => {
      return items.map((item, index) => {
        if (item === null) return null
        return endIndex > startIndex
          ? {
              ...item,
              isSelected: startIndex <= index && index <= endIndex,
            }
          : {
              ...item,
              isSelected: endIndex <= index && index <= startIndex,
            }
      })
    })
  }

  const reset = () => {
    setCursor(null)
    setListItems((items) => items.map((item) => makeListItem(item?.item ?? null)))
  }

  return { listItems, select, selectOnly, discard, selectTo, reset, toggle, cursor }
}
