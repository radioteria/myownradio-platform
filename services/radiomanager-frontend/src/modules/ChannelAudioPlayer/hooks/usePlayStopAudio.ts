import { MutableRefObject, useEffect, useReducer, useRef } from 'react'
import { BACKEND_BASE_URL } from '@/api'
import { playAudio, stopAudio } from '@/utils/audio'
import { useNowPlaying } from '@/modules/NowPlaying'
import { Duration, ZERO } from '@/utils/duration'

const POSITION_TOLERANCE = Duration.fromMillis(500)

const filterBelow = (value: number, threshold: number) => (value < threshold ? 0 : value)

export const usePlayStopAudio = (audioRef: MutableRefObject<HTMLAudioElement | null>) => {
  const { nowPlaying, updatedAt } = useNowPlaying()
  const currentAudioOffsetRef = useRef(ZERO)

  // Triggers Audio Player Restart
  const [restarted, restart] = useReducer((n) => n + 1, 0)

  const currentTrackId = nowPlaying?.currentTrack.track_id ?? null

  useEffect(() => {
    const audioElement = audioRef.current
    if (!audioElement || !nowPlaying || !currentTrackId) return

    const timeSinceLastUpdate = Duration.fromMillis(Date.now() - updatedAt.getTime())
    const currentTrackOffset = Duration.fromMillis(nowPlaying.currentTrack.offset)

    const playbackPosition = currentTrackOffset.add(timeSinceLastUpdate)
    const filteredPlaybackPosition = playbackPosition.filterBelow(POSITION_TOLERANCE)

    const url = new URL(
      `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${currentTrackId}/transcode`,
    )
    url.searchParams.set('initialPosition', String(filteredPlaybackPosition.toMillis()))

    let timeoutId: number | null = null
    const handlePlaybackError = () => {
      timeoutId = window.setTimeout(() => restart(), 1_000)
    }

    audioElement.addEventListener('error', handlePlaybackError)
    playAudio(audioElement, url.toString())

    currentAudioOffsetRef.current = filteredPlaybackPosition

    return () => {
      timeoutId && window.clearTimeout(timeoutId)

      audioElement.removeEventListener('error', handlePlaybackError)
      stopAudio(audioElement)
    }
  }, [currentTrackId, audioRef, restarted])

  return currentAudioOffsetRef
}
