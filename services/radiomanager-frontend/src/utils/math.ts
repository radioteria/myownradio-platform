export function clamp(min: number, current: number, max: number): number {
  return Math.max(min, Math.min(current, max))
}

export function scale(value: number, valueMax: number, scaleMax: number): number {
  return (scaleMax / valueMax) * value
}
