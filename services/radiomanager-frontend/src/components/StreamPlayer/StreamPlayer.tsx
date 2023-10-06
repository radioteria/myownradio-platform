import { useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { composeStreamMediaSource, CompositorEventType } from './Compositor'
import { browserFeatures } from '@/features'
import { useUserEvent } from '@/context/UserEventProvider'
import { UserEventType } from '@/api/pubsub/UserEvents'

const debug = makeDebug('StreamPlayer')

const BUFFER_AHEAD_TIME = 30_000 // 30 seconds

interface Props {
  readonly channelId: number
  readonly onTrackChanged?: (title: string) => void
}

export const StreamPlayer: React.FC<Props> = ({ channelId, onTrackChanged }) => {
  const audioElementRef = useRef<HTMLAudioElement>(null)

  const currentTime = useRef(0)
  const bufferedTime = useRef(0)

  const trackTitlesQueueRef = useRef<{ title: string; pts: number }[]>([])

  useEffect(() => {
    const audioElement = audioElementRef.current

    if (!audioElement) return

    const resetTime = () => {
      bufferedTime.current = 0
      currentTime.current = 0
    }

    const handleEnded = () => {
      resetTime()
      audioElement
        .play()
        .catch((event) => debug('Unable to restart stream playback on ended', event))
    }

    const handleError = (errorEvent: Event) => {
      resetTime()
      audioElement
        .play()
        .catch((event) => debug('Unable to restart stream playback on error', errorEvent, event))
    }

    const handleTimeUpdate = () => {
      currentTime.current = audioElement.currentTime

      const firstTitleInQueue = trackTitlesQueueRef.current.at(0)
      if (firstTitleInQueue && firstTitleInQueue.pts <= audioElement.currentTime) {
        onTrackChanged?.(firstTitleInQueue.title)
        trackTitlesQueueRef.current.shift()
      }

      if (audioElement.buffered.length > 0) {
        bufferedTime.current = audioElement.buffered.end(0)
      }
    }

    audioElement.addEventListener('ended', handleEnded)
    audioElement.addEventListener('error', handleError)
    audioElement.addEventListener('timeupdate', handleTimeUpdate)

    const mediaSource = composeStreamMediaSource(channelId, {
      bufferAheadTime: BUFFER_AHEAD_TIME,
      supportedCodecs: browserFeatures().supportedAudioCodecs,
      onCompositorEvent: async (event) => {
        switch (event.event) {
          case CompositorEventType.Metadata:
            trackTitlesQueueRef.current.push({ title: event.title, pts: event.pts })
            break

          default:
        }
      },
    })
    const objectURL = URL.createObjectURL(mediaSource)

    audioElement.src = objectURL
    audioElement.play().catch((event) => debug('Unable to restart stream playback', event))

    return () => {
      audioElement.removeEventListener('ended', handleEnded)
      audioElement.removeEventListener('error', handleError)
      audioElement.removeEventListener('timeupdate', handleTimeUpdate)

      resetTime()

      audioElement.pause()
      audioElement.load()
      audioElement.removeAttribute('src')

      URL.revokeObjectURL(objectURL)
    }
  }, [channelId, onTrackChanged])

  const userEventSource = useUserEvent()

  useEffect(() => {
    const audioElement = audioElementRef.current
    if (!audioElement) return

    return userEventSource.subscribe((msg) => {
      if (msg.eventType === UserEventType.RestartChannel && msg.channelId === channelId) {
        // TODO Restart player
        debug('Signal')
      }
    })
  }, [channelId, userEventSource])

  return <audio ref={audioElementRef} />
}
