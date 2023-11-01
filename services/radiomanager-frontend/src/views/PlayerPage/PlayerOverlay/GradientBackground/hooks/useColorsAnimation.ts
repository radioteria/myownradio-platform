import { useRunningTime } from './useRunningTime'
import { colors } from '../palette'
import { triangle, rand } from '../functions'

const SLIDE_DURATION_MILLIS = 30_000
const FRAME_DURATION_MILLIS = SLIDE_DURATION_MILLIS * 2

export interface ColorsAnimationState {
  frameAOpacity: number
  frameAColors: readonly [string, string, string, string]
  frameBOpacity: number
  frameBColors: readonly [string, string, string, string]
}

export const useColorsAnimation = (initialTimeMillis: number): ColorsAnimationState => {
  const runningTimeMillis = useRunningTime(initialTimeMillis, false)

  const frameAOpacity = triangle(runningTimeMillis + SLIDE_DURATION_MILLIS, FRAME_DURATION_MILLIS)
  const frameBOpacity = triangle(runningTimeMillis, FRAME_DURATION_MILLIS)

  const colorA = Math.floor(runningTimeMillis / FRAME_DURATION_MILLIS)
  const colorB = Math.floor((runningTimeMillis + SLIDE_DURATION_MILLIS) / FRAME_DURATION_MILLIS)

  const frameAColors = [
    colors[Math.floor(rand(colorA) * colors.length)],
    colors[Math.floor(rand((colorA + 1) * 2) * colors.length)],
    colors[Math.floor(rand((colorA + 2) * 3) * colors.length)],
    colors[Math.floor(rand((colorA + 3) * 4) * colors.length)],
  ] as const
  const frameBColors = [
    colors[Math.floor(rand(colorB) * colors.length)],
    colors[Math.floor(rand((colorB + 1) * 2) * colors.length)],
    colors[Math.floor(rand((colorB + 2) * 3) * colors.length)],
    colors[Math.floor(rand((colorB + 3) * 4) * colors.length)],
  ] as const

  return { frameAOpacity, frameBOpacity, frameAColors, frameBColors }
}
