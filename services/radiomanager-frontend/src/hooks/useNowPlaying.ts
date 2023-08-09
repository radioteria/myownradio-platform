import { useEffect, useState } from 'react'
import { getNowPlaying } from '@/api'
import { NowPlaying } from '@/api.types'

export function useNowPlaying(channelId: number) {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)

  useEffect(() => {
    let timeoutId: null | number = null

    const check = () => {
      getNowPlaying(channelId, Date.now()).then((np) => {
        setNowPlaying(np)
        timeoutId = window.setTimeout(
          check,
          Math.min(30_000, np.currentTrack.duration - np.currentTrack.offset),
        )
      })
    }

    return () => {
      timeoutId && window.clearTimeout(timeoutId)
    }
  }, [channelId])

  return nowPlaying
}
