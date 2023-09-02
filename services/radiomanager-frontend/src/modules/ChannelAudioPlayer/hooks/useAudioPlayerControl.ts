import { MutableRefObject, useEffect, useReducer, useRef } from 'react'
import { BACKEND_BASE_URL } from '@/api'
import { playAudio, stopAudio } from '@/utils/audio'
import { useNowPlaying } from '@/modules/NowPlaying'
import { Duration, ZERO } from '@/utils/duration'
import { useAudioRestartOnError } from '@/modules/ChannelAudioPlayer/hooks/useAudioRestartOnError'

const POSITION_TOLERANCE = Duration.fromMillis(500)

const filterBelow = (value: number, threshold: number) => (value < threshold ? 0 : value)

export const useAudioPlayerControl = (audioRef: MutableRefObject<HTMLAudioElement | null>) => {
  const { nowPlaying, updatedAt } = useNowPlaying()
  const currentAudioOffsetRef = useRef(ZERO)
  const numRestartsOnError = useAudioRestartOnError(audioRef)

  const currentTrackId = nowPlaying?.currentTrack.track_id ?? null

  const [numRestarts, restart] = useReducer((n: number) => n + 1, 0)
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

    playAudio(audioElement, url.toString())

    currentAudioOffsetRef.current = filteredPlaybackPosition

    return () => {
      stopAudio(audioElement)
    }
  }, [currentTrackId, audioRef, numRestartsOnError, numRestarts])

  return { currentAudioOffsetRef, restart }
}
