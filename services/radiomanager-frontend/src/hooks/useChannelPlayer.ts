import { MutableRefObject, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { useNowPlaying } from '@/modules/NowPlaying'
import { advanceAudio, isAudioStopped, playAudio, stopAudio } from '@/utils/audio'
import { BACKEND_BASE_URL } from '@/api'

const debug = makeDebug('useChannelPlayer')

export const useChannelPlayer = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  isStopped: boolean,
) => {
  const { nowPlaying } = useNowPlaying()

  const currentAudioOffsetRef = useRef(0)
  const currentTrackIdRef = useRef(0)

  const currentTrackId = nowPlaying?.currentTrack.track_id
  const currentTrackOffset = nowPlaying?.currentTrack.offset

  // Stop Effect
  useEffect(() => {
    if (
      audioRef.current &&
      (nowPlaying === null || isStopped) &&
      !isAudioStopped(audioRef.current)
    ) {
      stopAudio(audioRef.current)
      currentTrackIdRef.current = 0
    }
  }, [nowPlaying, audioRef, isStopped])

  // Play Effect
  useEffect(() => {
    if (isStopped || !currentTrackId || !currentTrackOffset || !audioRef.current) {
      return
    }

    if (currentTrackIdRef.current !== currentTrackId) {
      const url = new URL(
        `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${currentTrackId}/transcode`,
      )
      url.searchParams.set('initialPosition', String(currentTrackOffset))

      playAudio(audioRef.current, url.toString())

      currentTrackIdRef.current = currentTrackId
      currentAudioOffsetRef.current = currentTrackOffset
    }
  }, [currentTrackId, currentTrackOffset, audioRef, isStopped])

  // Sync Effect
  useEffect(() => {
    if (isStopped || !audioRef.current || currentTrackOffset === undefined) {
      return
    }

    const currentAudioTime = audioRef.current.currentTime * 1000 + currentAudioOffsetRef.current
    const delay = (currentTrackOffset - currentAudioTime) / 1000

    debug('Player positions delta: %f', delay)

    if (Math.abs(delay) > 1) {
      advanceAudio(audioRef.current, delay)
      // currentAudioOffsetRef.current = currentTrackOffset
    }
  }, [currentTrackOffset, audioRef, isStopped])
}
