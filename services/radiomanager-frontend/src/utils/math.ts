export function clamp(min: number, current: number, max: number): number {
  return Math.max(min, Math.min(current, max))
}

export function scale(value: number, valueMax: number, scaleMax: number): number {
  return (scaleMax / valueMax) * value
}

export function filterBelow(value: number, threshold: number): number {
  return value < threshold ? 0 : value
}

export function quantise(value: number, ratio: number): number {
  return Math.floor(value / ratio) * ratio
}
