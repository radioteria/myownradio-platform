import { MutableRefObject, useEffect } from 'react'
import makeDebug from 'debug'
import { Duration } from '@/utils/duration'
import { advanceAudio } from '@/utils/audio'
import { usePlaybackPosition } from '@/modules/NowPlaying'

const debug = makeDebug('useChannelPlayer')

const LATENCY_TOLERANCE = Duration.fromMillis(500)

export const useAudioPlayerSync = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  currentAudioOffsetRef: MutableRefObject<Duration>,
) => {
  const playbackPosition = usePlaybackPosition()

  useEffect(() => {
    if (!audioRef.current || playbackPosition === null) {
      return
    }

    const currentAudioTime = Duration.fromSeconds(audioRef.current?.currentTime ?? 0)
    const audioPosition = currentAudioOffsetRef.current.add(currentAudioTime)

    const delay = playbackPosition.sub(audioPosition)

    debug('Latency: %s', delay.toSeconds().toFixed(3))

    if (Math.abs(delay.toMillis()) > LATENCY_TOLERANCE.toMillis()) {
      debug('Audio latency > %s (%s)', LATENCY_TOLERANCE, delay)
      advanceAudio(audioRef.current, delay.toSeconds())
    }
  }, [playbackPosition, audioRef, currentAudioOffsetRef])
}
