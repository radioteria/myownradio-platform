import { MutableRefObject, useEffect } from 'react'
import makeDebug from 'debug'
import { Duration } from '@/utils/duration'
import { advanceAudio } from '@/utils/audio'
import { useEstimatedTrackPosition } from '@/modules/NowPlaying'

const debug = makeDebug('useChannelPlayer')

const LATENCY_TOLERANCE = Duration.fromMillis(500)

export const useAudioPlayerSync = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  currentAudioOffsetRef: MutableRefObject<Duration>,
  onRestart: () => void,
) => {
  const estimatedTrackPosition = useEstimatedTrackPosition()

  useEffect(() => {
    if (!audioRef.current || estimatedTrackPosition === null) {
      return
    }

    const currentPlayerPosition = Duration.fromSeconds(audioRef.current?.currentTime ?? 0)
    const currentAudioPosition = currentAudioOffsetRef.current.add(currentPlayerPosition)

    const latency = estimatedTrackPosition.sub(currentAudioPosition)

    debug('Latency: %s', latency.toSeconds().toFixed(3))

    if (currentPlayerPosition.add(latency).isNeg()) {
      debug('Audio latency is negative and exceeds current audio position.')
      return onRestart()
    }

    if (Math.abs(latency.toMillis()) > LATENCY_TOLERANCE.toMillis()) {
      debug('Audio latency > %s (%s)', LATENCY_TOLERANCE, latency)
      advanceAudio(audioRef.current, latency.toSeconds())
    }
  }, [estimatedTrackPosition, audioRef, currentAudioOffsetRef])
}
