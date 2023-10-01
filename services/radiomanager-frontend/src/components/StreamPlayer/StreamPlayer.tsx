import { useEffect, useRef } from 'react'
import { composeStreamMediaSource } from './Compositor'
import { browserFeatures } from '@/features'
import makeDebug from 'debug'

const debug = makeDebug('StreamPlayer')

const BUFFER_AHEAD_TIME = 30_000 // 30 seconds

interface Props {
  readonly channelId: number
}

export const StreamPlayer: React.FC<Props> = ({ channelId }) => {
  const audioElementRef = useRef<HTMLAudioElement>(null)

  const currentTime = useRef(0)
  const bufferedTime = useRef(0)

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
  }, [channelId])

  return <audio ref={audioElementRef} />
}
