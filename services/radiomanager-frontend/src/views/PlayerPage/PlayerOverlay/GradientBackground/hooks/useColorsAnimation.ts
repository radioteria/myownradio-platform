import { useRunningTime } from './useRunningTime'
import { colors } from '../palette'

const FRAME_DURATION_MILLIS = 60_000
const TIME_PERIOD = FRAME_DURATION_MILLIS * 2

export interface ColorsAnimationState {
  frameAOpacity: number
  frameAColors: readonly [string, string, string, string]
  frameBOpacity: number
  frameBColors: readonly [string, string, string, string]
}

const rand = (seed: number): number => {
  const x = Math.sin(seed) * 70000
  return x - Math.floor(x)
}

export const useColorsAnimation = (initialTimeMillis: number): ColorsAnimationState => {
  const runningTimeMillis = useRunningTime(initialTimeMillis, false)
  const keyframe = runningTimeMillis % TIME_PERIOD

  const frameAOpacity = Math.min(keyframe, TIME_PERIOD - keyframe) * 0.0002
  const frameBOpacity =
    ((keyframe < FRAME_DURATION_MILLIS ? TIME_PERIOD - keyframe : keyframe) -
      FRAME_DURATION_MILLIS) *
    0.0002

  const colorAIndex = Math.floor(runningTimeMillis / TIME_PERIOD)
  const colorBIndex = Math.max(
    0,
    Math.floor((runningTimeMillis - FRAME_DURATION_MILLIS) / TIME_PERIOD),
  )

  const frameAColors = [
    colors[Math.floor(rand(colorAIndex) * colors.length)],
    colors[Math.floor(rand(colorAIndex + 1) * colors.length)],
    colors[Math.floor(rand(colorAIndex + 2) * colors.length)],
    colors[Math.floor(rand(colorAIndex + 3) * colors.length)],
  ] as const
  const frameBColors = [
    colors[Math.floor(rand(colorBIndex) * colors.length)],
    colors[Math.floor(rand(colorBIndex + 1) * colors.length)],
    colors[Math.floor(rand(colorBIndex + 2) * colors.length)],
    colors[Math.floor(rand(colorBIndex + 3) * colors.length)],
  ] as const

  return { frameAOpacity, frameBOpacity, frameAColors, frameBColors }
}
