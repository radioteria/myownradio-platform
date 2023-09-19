import { Interval } from './types'

export const numbersToExclusiveIntervals = (arr: number[]) => {
  if (arr.length === 0) return []

  const sortedArr = [...arr.sort((a, b) => a - b)]
  const intervals: Interval[] = []

  let start = sortedArr[0]
  let end = sortedArr[0] + 1

  for (let i = 1; i < sortedArr.length; i++) {
    if (sortedArr[i] === end) {
      end++
    } else {
      intervals.push({ start, end })
      start = sortedArr[i]
      end = sortedArr[i] + 1
    }
  }

  intervals.push({ start, end })
  return intervals
}
