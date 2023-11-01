import { useEffect, useState } from 'react'

export const useRunningTime = (initialTimeMillis: number, paused: boolean): number => {
  const [time, setTime] = useState(initialTimeMillis)

  useEffect(() => {
    setTime(initialTimeMillis)

    if (paused) {
      return
    }

    const start = performance.now()
    const intervalId = window.setInterval(() => {
      const delta = performance.now() - start
      setTime(initialTimeMillis + delta)
    }, 250)

    return () => {
      window.clearInterval(intervalId)
    }
  }, [initialTimeMillis, paused])

  return time
}
