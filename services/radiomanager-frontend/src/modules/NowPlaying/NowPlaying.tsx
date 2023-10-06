import { createContext, ReactNode, useContext, useEffect, useReducer, useState } from 'react'
import { NowPlaying, getNowPlaying } from '@/api'
import { useUserEvent } from '@/context/UserEventProvider'
import { UserEventType } from '@/api/pubsub/UserEvents'

export const NowPlayingContext = createContext<{
  readonly updatedAt: Date
  readonly nowPlaying: NowPlaying | null
  readonly refresh: () => void
  readonly channelId: number
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

  const userEventSource = useUserEvent()
  useEffect(() => {
    return userEventSource.subscribe((event) => {
      if (event.eventType === UserEventType.RestartChannel && event.channelId === channelId) {
        refresh()
      }
    })
  }, [refresh, channelId, userEventSource])

  useEffect(() => {
    let timeoutId: null | number = null
    let isComponentUnmounted = false

    const fetchAndUpdateNowPlaying = async () => {
      let nextUpdateDelay = UPDATE_INTERVAL

      const ts = new Date()

      try {
        const nowPlayingData = await getNowPlaying(channelId, ts.getTime())

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

      setUpdatedAt(ts)

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
    <NowPlayingContext.Provider value={{ nowPlaying, refresh, updatedAt, channelId }}>
      {children}
    </NowPlayingContext.Provider>
  )
}
