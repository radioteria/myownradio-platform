import { useEffect, useState } from 'react'
import { getNowPlaying } from '@/api/api.client'
import { NowPlaying } from '@/api/api.types'

export function useNowPlaying(channelId: number) {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)

  useEffect(() => {
    let timeoutId: null | number = null
    let removed = false

    const check = () => {
      getNowPlaying(channelId, Date.now())
        .then((np) => {
          if (removed) {
            return null
          }

          setNowPlaying(np)

          return Math.min(5_000, np.currentTrack.duration - np.currentTrack.offset)
        })
        .catch(() => 5_000)
        .then((timeout) => {
          if (timeout && !removed) {
            timeoutId = window.setTimeout(check, Math.min(5_000, timeout))
          }
        })
    }

    check()

    return () => {
      removed = true
      timeoutId && window.clearTimeout(timeoutId)
    }
  }, [channelId])

  return nowPlaying
}
