import { useEffect, useReducer, useState } from 'react'
import { getNowPlaying } from '@/api/api.client'
import { NowPlaying } from '@/api/api.types'

const UPDATE_INTERVAL = 5_000

export function useNowPlaying(channelId: number) {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)
  const [updated, update] = useReducer((x) => x + 1, 0)

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

          return Math.min(UPDATE_INTERVAL, np.currentTrack.duration - np.currentTrack.offset)
        })
        .catch(() => UPDATE_INTERVAL)
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
  }, [channelId, updated])

  return { nowPlaying, update }
}
