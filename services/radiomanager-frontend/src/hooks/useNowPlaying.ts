import { useEffect, useState } from 'react'
import { getNowPlaying } from '@/api/api.client'
import { NowPlaying } from '@/api/api.types'

export function useNowPlaying(channelId: number) {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)

  useEffect(() => {
    let timeoutId: null | number = null
    let removed = false

    const check = () => {
      getNowPlaying(channelId, Date.now()).then((np) => {
        if (removed) {
          return
        }

        setNowPlaying(np)

        timeoutId = window.setTimeout(
          check,
          Math.min(5_000, np.currentTrack.duration - np.currentTrack.offset),
        )
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
