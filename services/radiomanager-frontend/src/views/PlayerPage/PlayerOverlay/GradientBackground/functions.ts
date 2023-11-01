export const sine = (time: number, period: number): number => {
  const x = (time / period) * 2 * Math.PI

  return (Math.sin(x) + 1) / 2
}

export const sawtooth = (time: number, period: number): number => {
  const x = time % period

  return x / period
}

export const triangle = (time: number, period: number): number => {
  const x = (time / period) * 2 * Math.PI

  return (Math.asinh(Math.cos(x)) + 1) / 2
}

export const rand = (time: number): number => {
  const x = Math.sin(time) * 70000

  return x - Math.floor(x)
}
