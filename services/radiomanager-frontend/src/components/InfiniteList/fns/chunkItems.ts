import { isNull } from '@/utils/fun'

type Chunk<Item extends NonNullable<unknown>> = {
  hasNull: boolean
  items: [Item | null, number][]
}

export const chunkItems = <Item extends NonNullable<unknown>>(
  items: readonly (Item | null)[],
  chunkSize: number,
): Chunk<Item>[] => {
  let chunks: Chunk<Item>[] = []
  let currentChunk: Chunk<Item> | null = null

  for (let i = 0; i < items.length; i += 1) {
    if (currentChunk === null || (items[i] === null && currentChunk.items.length >= chunkSize)) {
      currentChunk = { items: [], hasNull: false }
      chunks.push(currentChunk)
    }

    currentChunk.items.push([items[i], i])
    currentChunk.hasNull ||= isNull(items[i])
  }

  return chunks
}
