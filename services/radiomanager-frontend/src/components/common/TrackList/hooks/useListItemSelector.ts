import { useEffect, useState } from 'react'

interface ListItem<I> {
  item: I
  isSelected: boolean
}

const makeListItem = <I>(item: I) => ({ item, isSelected: false })

const mapToListItems = <T>(items: readonly T[]) => {
  return items.map(makeListItem)
}

export const useListItemSelector = <I>(initialItems: readonly I[]) => {
  const [listItems, setListItems] = useState<readonly ListItem<I>[]>(mapToListItems(initialItems))
  const [cursor, setCursor] = useState<null | number>(null)

  // Reset selection on tracks list update.
  useEffect(() => {
    setListItems((prevItems) => {
      const prevItemsMap = new Map(prevItems.map((item) => [item.item, item]))

      return initialItems.map((item) => {
        const prevItem = prevItemsMap.get(item)
        return prevItem ? { ...prevItem, item } : makeListItem(item)
      })
    })
  }, [initialItems])

  const select = (selectIndex: number) => {
    setCursor(selectIndex)
    setListItems((items) =>
      items.map((item, index) => {
        return selectIndex === index ? { ...item, isSelected: true } : item
      }),
    )
  }

  const selectOnly = (selectIndex: number) => {
    setCursor(selectIndex)
    setListItems((items) =>
      items.map((item, index) => {
        return { ...item, isSelected: selectIndex === index }
      }),
    )
  }

  const discard = (discardIndex: number) => {
    setCursor(discardIndex)
    setListItems((items) =>
      items.map((item, index) => {
        return discardIndex === index ? { ...item, isSelected: false } : item
      }),
    )
  }

  const selectTo = (endIndex: number) => {
    const startIndex = cursor ?? 0

    setListItems((items) =>
      items.map((item, index) => {
        return startIndex <= index && index <= endIndex ? { ...item, isSelected: true } : item
      }),
    )
  }

  const reset = () => {
    setCursor(null)
    setListItems((items) => items.map(({ item }) => makeListItem(item)))
  }

  return { listItems, select, selectOnly, discard, selectTo, reset }
}
