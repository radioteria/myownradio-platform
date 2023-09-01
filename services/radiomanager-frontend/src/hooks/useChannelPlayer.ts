import { MutableRefObject, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { useNowPlaying, usePlaybackPosition } from '@/modules/NowPlaying'
import { advanceAudio, playAudio, stopAudio } from '@/utils/audio'
import { BACKEND_BASE_URL } from '@/api'
import { Duration, ZERO } from '@/utils/duration'

const debug = makeDebug('useChannelPlayer')

const LATENCY_TOLERANCE = Duration.fromMillis(500)
const POSITION_TOLERANCE = Duration.fromMillis(250)

export const useChannelPlayer = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  isStopped: boolean,
) => {
  const { nowPlaying, updatedAt } = useNowPlaying()
  const playbackPosition = usePlaybackPosition()

  const currentAudioOffsetRef = useRef(ZERO)

  const currentTrackId = nowPlaying?.currentTrack.track_id ?? null

  // Start / Stop Playback Effect
  useEffect(() => {
    const audioElement = audioRef.current
    if (!audioElement || isStopped) return
    if (playbackPosition === null || currentTrackId === null) return

    const filteredPlaybackPosition = playbackPosition.filterBelow(POSITION_TOLERANCE)
    const url = new URL(
      `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${currentTrackId}/transcode`,
    )
    url.searchParams.set('initialPosition', String(filteredPlaybackPosition.toMillis()))
    playAudio(audioElement, url.toString())

    currentAudioOffsetRef.current = filteredPlaybackPosition

    return () => {
      stopAudio(audioElement)
    }
  }, [currentTrackId, isStopped, audioRef])

  // Sync Effect
  useEffect(() => {
    if (isStopped || !audioRef.current || playbackPosition === null) {
      return
    }

    const currentAudioTime = Duration.fromSeconds(audioRef.current?.currentTime ?? 0)
    const audioPosition = currentAudioOffsetRef.current.add(currentAudioTime)

    const delay = playbackPosition.sub(audioPosition)

    if (Math.abs(delay.toMillis()) > LATENCY_TOLERANCE.toMillis()) {
      debug('Audio latency > %s (%s)', LATENCY_TOLERANCE, delay)
      advanceAudio(audioRef.current, delay.toSeconds())
    }
  }, [playbackPosition, isStopped, audioRef])
}
