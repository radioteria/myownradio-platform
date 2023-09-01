import { MutableRefObject, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { useNowPlaying, usePlaybackPosition } from '@/modules/NowPlaying'
import { advanceAudio, isAudioStopped, playAudio, stopAudio } from '@/utils/audio'
import { BACKEND_BASE_URL } from '@/api'

const debug = makeDebug('useChannelPlayer')

export const useChannelPlayer = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  isStopped: boolean,
) => {
  const { nowPlaying } = useNowPlaying()
  const playbackPosition = usePlaybackPosition()

  const currentAudioOffsetRef = useRef(0)

  const currentTrackId = nowPlaying?.currentTrack.track_id ?? null

  // Start / Stop Playback Effect
  useEffect(() => {
    const audioElement = audioRef.current
    if (!audioElement || isStopped) return
    if (playbackPosition === null || currentTrackId === null) return

    const url = new URL(
      `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${currentTrackId}/transcode`,
    )
    url.searchParams.set('initialPosition', String(playbackPosition))

    playAudio(audioElement, url.toString())
    currentAudioOffsetRef.current = playbackPosition

    return () => {
      stopAudio(audioElement)
    }
  }, [currentTrackId, isStopped, audioRef])

  // Sync Effect
  useEffect(() => {
    if (isStopped || !audioRef.current || playbackPosition === undefined) {
      return
    }

    const currentAudioTimeMillis = audioRef.current?.currentTime ?? 0
    const audioPosition = currentAudioOffsetRef.current / 1000 + currentAudioTimeMillis

    const delay = (playbackPosition ?? 0) / 1000 - audioPosition

    if (Math.abs(delay) > 0.25) {
      debug('Audio latency > 250ms')
      advanceAudio(audioRef.current, delay)
      currentAudioOffsetRef.current += delay
    }
  }, [playbackPosition, isStopped, audioRef])
}
