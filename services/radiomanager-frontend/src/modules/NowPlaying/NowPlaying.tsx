import { createContext, ReactNode, useContext, useEffect, useReducer, useState } from 'react'
import { NowPlaying, getNowPlaying } from '@/api'

export const NowPlayingContext = createContext<{
  updatedAt: Date
  nowPlaying: NowPlaying | null
  refresh: () => void
} | null>(null)

const UPDATE_INTERVAL = 10_000

interface Props {
  readonly channelId: number
  readonly children: ReactNode
}

export const NowPlayingProvider: React.FC<Props> = ({ channelId, children }) => {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)
  const [refreshed, refresh] = useReducer((x) => x + 1, 0)
  const [updatedAt, setUpdatedAt] = useState(new Date())

  useEffect(() => {
    let timeoutId: null | number = null
    let isComponentUnmounted = false

    const fetchAndUpdateNowPlaying = async () => {
      let nextUpdateDelay = UPDATE_INTERVAL

      try {
        const nowPlayingData = await getNowPlaying(channelId, Date.now())

        if (isComponentUnmounted) {
          return
        }

        nextUpdateDelay = Math.min(
          UPDATE_INTERVAL,
          nowPlayingData.currentTrack.duration - nowPlayingData.currentTrack.offset,
        )

        setNowPlaying(nowPlayingData)
      } catch (e) {
        setNowPlaying(null)
      }

      setUpdatedAt(new Date())

      timeoutId = window.setTimeout(fetchAndUpdateNowPlaying, nextUpdateDelay)
    }

    // Initial update
    fetchAndUpdateNowPlaying().catch(() => {})

    return () => {
      isComponentUnmounted = true
      if (timeoutId) {
        window.clearTimeout(timeoutId)
      }
    }
  }, [channelId, refreshed])

  return (
    <NowPlayingContext.Provider value={{ nowPlaying, refresh, updatedAt }}>
      {children}
    </NowPlayingContext.Provider>
  )
}
