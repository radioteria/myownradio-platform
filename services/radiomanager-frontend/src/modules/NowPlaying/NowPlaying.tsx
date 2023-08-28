import { createContext, ReactNode, useContext, useEffect, useReducer, useState } from 'react'
import { NowPlaying } from '@/api/api.types'
import { getNowPlaying } from '@/api/api.client'

const NowPlayingContext = createContext<{
  nowPlaying: NowPlaying | null
  update: () => void
} | null>(null)

export const useNowPlaying = () => {
  const ctx = useContext(NowPlayingContext)

  if (!ctx) {
    throw new Error('Now playing data has not been found in the context')
  }

  return ctx
}

const UPDATE_INTERVAL = 10_000

interface Props {
  readonly channelId: number
  readonly children: ReactNode
}

export const NowPlayingProvider: React.FC<Props> = ({ channelId, children }) => {
  const [nowPlaying, setNowPlaying] = useState<null | NowPlaying>(null)
  const [updated, update] = useReducer((x) => x + 1, 0)

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
  }, [channelId, updated])

  return (
    <NowPlayingContext.Provider value={{ nowPlaying, update }}>
      {children}
    </NowPlayingContext.Provider>
  )
}
