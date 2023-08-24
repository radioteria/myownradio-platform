import { PrimitiveAtom, createStore, atom, Atom } from 'jotai'

type Store = ReturnType<typeof createStore>

export const pushToArrayAtom = <Value>(
  atom: PrimitiveAtom<readonly Value[]>,
  item: Value,
  { get, set }: Store,
) => {
  const items = get(atom)
  const newItems = [...items, item]
  set(atom, newItems)
}

export const popFromArrayAtom = <Value>(
  atom: PrimitiveAtom<readonly Value[]>,
  { get, set }: Store,
) => {
  const [firstItem, ...restItems] = get(atom)
  if (!firstItem) {
    return null
  }
  set(atom, restItems)
  return firstItem
}
