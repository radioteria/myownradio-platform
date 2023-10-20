import { useCallback, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { composeStreamMediaSource, CompositorEventType } from './Compositor'
import { browserFeatures } from '@/features'
import { useUserEvent } from '@/context/UserEventProvider'
import { UserEventType } from '@/api/pubsub/UserEvents'

const debug = makeDebug('StreamPlayer')

const BUFFER_AHEAD_TIME = 30_000 // 30 seconds

interface Props {
  readonly channelId: number
  readonly muted: boolean
  readonly onTrackChanged?: (title: string) => void
}

export const StreamPlayer: React.FC<Props> = ({ channelId, onTrackChanged, muted }) => {
  const audioElement1Ref = useRef<HTMLAudioElement>(null)
  const audioElement2Ref = useRef<HTMLAudioElement>(null)
  const activeAudioElementIndexRef = useRef(0)
  const getActiveAudioElement = useCallback(
    () =>
      activeAudioElementIndexRef.current === 0
        ? audioElement1Ref.current
        : audioElement2Ref.current,
    [],
  )
  const toggleAudioElements = useCallback(() => {
    activeAudioElementIndexRef.current += 1
    activeAudioElementIndexRef.current %= 2
  }, [])

  const currentTime = useRef(0)
  const bufferedTime = useRef(0)

  const trackTitlesQueueRef = useRef<{ title: string; pts: number }[]>([])
  const currentObjectURL = useRef<string | null>(null)

  const updateTitle = useCallback(
    (audioElement: HTMLAudioElement) => {
      const firstTitleInQueue = trackTitlesQueueRef.current.at(0)
      if (firstTitleInQueue && firstTitleInQueue.pts <= audioElement.currentTime) {
        onTrackChanged?.(firstTitleInQueue.title)
        trackTitlesQueueRef.current.shift()
      }
    },
    [onTrackChanged],
  )

  const handleEnded = useCallback(() => {
    const audioElement = getActiveAudioElement()
    if (!audioElement) return

    audioElement.play().catch((event) => debug('Unable to restart stream playback on ended', event))
  }, [getActiveAudioElement])

  const handleError = useCallback(
    (errorEvent: ErrorEvent) => {
      const audioElement = getActiveAudioElement()
      if (!audioElement) return

      audioElement
        .play()
        .catch((event) => debug('Unable to restart stream playback on error', errorEvent, event))
    },
    [getActiveAudioElement],
  )

  const handleTimeUpdate = useCallback(() => {
    const audioElement = getActiveAudioElement()
    if (!audioElement) return

    currentTime.current = audioElement.currentTime

    updateTitle(audioElement)

    if (audioElement.buffered.length > 0) {
      bufferedTime.current = audioElement.buffered.end(0)
    }
  }, [updateTitle, getActiveAudioElement])

  const stop = useCallback(
    (audioElement: HTMLAudioElement) => {
      audioElement.removeEventListener('ended', handleEnded)
      audioElement.removeEventListener('error', handleError)
      audioElement.removeEventListener('timeupdate', handleTimeUpdate)

      if (currentObjectURL.current !== null) {
        URL.revokeObjectURL(currentObjectURL.current)
      }

      audioElement.pause()
      audioElement.load()
      audioElement.removeAttribute('src')
    },
    [handleError, handleEnded, handleTimeUpdate],
  )

  const play = useCallback(
    (audioElement: HTMLAudioElement) => {
      const mediaSource = composeStreamMediaSource(channelId, {
        bufferAheadTime: BUFFER_AHEAD_TIME,
        supportedCodecs: browserFeatures().supportedAudioCodecs,
        onCompositorEvent: async (event) => {
          switch (event.event) {
            case CompositorEventType.Metadata:
              trackTitlesQueueRef.current.push({ title: event.title, pts: event.pts })
              updateTitle(audioElement)
              break

            case CompositorEventType.Pause:
              audioElement1Ref.current && stop(audioElement1Ref.current)
              audioElement2Ref.current && stop(audioElement2Ref.current)
              break

            default:
          }
        },
      })

      const newObjectURL = URL.createObjectURL(mediaSource)

      audioElement.addEventListener('ended', handleEnded)
      audioElement.addEventListener('error', handleError)
      audioElement.addEventListener('timeupdate', handleTimeUpdate)

      audioElement.src = newObjectURL
      audioElement.play().catch((event) => debug('Unable to start stream playback', event))

      if (currentObjectURL.current !== null) {
        URL.revokeObjectURL(currentObjectURL.current)
      }

      currentObjectURL.current = newObjectURL
    },
    [channelId, updateTitle, stop, handleError, handleEnded, handleTimeUpdate],
  )

  useEffect(() => {
    const audioElement = getActiveAudioElement()
    if (!audioElement) return

    play(audioElement)

    return () => {
      stop(audioElement)
    }
  }, [channelId, onTrackChanged, stop, play, getActiveAudioElement])

  useEffect(() => {
    const audioElement1 = audioElement1Ref.current
    const audioElement2 = audioElement2Ref.current
    if (!audioElement1 || !audioElement2) return

    audioElement1.addEventListener('playing', () => stop(audioElement2))
    audioElement2.addEventListener('playing', () => stop(audioElement1))
  }, [stop])

  const userEventSource = useUserEvent()

  useEffect(() => {
    return userEventSource.subscribe((msg) => {
      if (msg.eventType === UserEventType.RestartChannel && msg.channelId === channelId) {
        debug('Restarting channel due to user event')
        toggleAudioElements()

        const newActiveAudioElement = getActiveAudioElement()
        if (newActiveAudioElement) {
          play(newActiveAudioElement)
        }
      }
    })
  }, [channelId, userEventSource, play, getActiveAudioElement, toggleAudioElements])

  return (
    <>
      <audio ref={audioElement1Ref} muted={muted} />
      <audio ref={audioElement2Ref} muted={muted} />
    </>
  )
}
