import { MutableRefObject, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { useNowPlaying } from '@/modules/NowPlaying'
import { seekAudio, loadAudio, playAudio, stopAudio } from '@/utils/audio'
import { BACKEND_BASE_URL } from '@/api'
import { filterBelow, scale } from '@/utils/math'

const debug = makeDebug('useChannelPlayer')

const START_TOLERANCE = 500

export const useChannelPlayer = (
  audio0Ref: MutableRefObject<HTMLAudioElement | null>,
  audio1Ref: MutableRefObject<HTMLAudioElement | null>,
  audioOffsetRef: MutableRefObject<number>,
) => {
  const nowPlayingData = useNowPlaying()
  const currentTrackId = nowPlayingData.nowPlaying?.currentTrack.trackId ?? null

  const isPlaying = !!nowPlayingData.nowPlaying
  const activeAudioRef = useRef(0)

  // Play Effect
  useEffect(() => {
    const audio0Element = audio0Ref.current
    const audio1Element = audio1Ref.current
    const { nowPlaying, updatedAt } = nowPlayingData

    if (!audio0Element || !audio1Element || !nowPlaying) return

    activeAudioRef.current = 1 - activeAudioRef.current
    const activeAudioElement = activeAudioRef.current === 0 ? audio0Element : audio1Element
    const inactiveAudioElement = activeAudioRef.current === 0 ? audio1Element : audio0Element
    debug('Active audio element: %d', activeAudioRef.current)

    const trackPosition = nowPlaying.currentTrack.offset
    const trackId = nowPlaying.currentTrack.trackId
    const trackDuration = nowPlaying.currentTrack.duration
    const timeSinceLastUpdate = Date.now() - updatedAt.getTime()
    const estimatedTrackPosition = filterBelow(trackPosition + timeSinceLastUpdate, START_TOLERANCE)
    const positionPercent = scale(estimatedTrackPosition, trackDuration, 100)

    const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${trackId}/transcode`)
    if (estimatedTrackPosition > 0) {
      url.searchParams.set('initialPosition', `${estimatedTrackPosition}`)
    }
    const src = url.toString()
    audioOffsetRef.current = estimatedTrackPosition
    debug(
      'Playing track %d starting from position %dms (%d%)',
      trackId,
      estimatedTrackPosition,
      positionPercent,
    )
    playAudio(activeAudioElement, src)

    const nextTrackId = nowPlaying.nextTrack.trackId
    const nextSrc = `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${nextTrackId}/transcode`
    debug('Preloading next track %s', nextTrackId)
    loadAudio(inactiveAudioElement, nextSrc)
  }, [audio0Ref, audioOffsetRef, audio1Ref, currentTrackId])

  // Stop Effect
  useEffect(() => {
    const audio1Element = audio0Ref.current
    const audio2Element = audio1Ref.current

    if (!isPlaying || !audio1Element || !audio2Element) return

    return () => {
      debug('Stopping audio playback')
      stopAudio(audio1Element)
      stopAudio(audio2Element)
    }
  }, [audio0Ref, audio1Ref, isPlaying])

  // Latency Effect
  useEffect(() => {
    const audio1Element = audio0Ref.current
    const audio2Element = audio1Ref.current

    const { nowPlaying, updatedAt } = nowPlayingData

    if (!audio1Element || !audio2Element || !nowPlaying) return

    const activeAudioElement = activeAudioRef.current === 0 ? audio1Element : audio2Element

    const trackPosition = nowPlaying.currentTrack.offset
    const timeSinceLastUpdate = Date.now() - updatedAt.getTime()

    const estimatedTrackPosition = filterBelow(trackPosition + timeSinceLastUpdate, START_TOLERANCE)
    const currentAudioTime = activeAudioElement.currentTime * 1000
    const currentTrackPosition = currentAudioTime + audioOffsetRef.current

    const latency = estimatedTrackPosition - currentTrackPosition

    debug('Latency: %dms', latency)

    if (latency < 0 && Math.abs(latency) > currentAudioTime) {
      debug('Audio latency is negative and exceeds current audio position.')
      // TODO: Restart
      return
    }

    if (Math.abs(latency) > 1_000) {
      debug('Advancing audio position by %dms', latency)

      seekAudio(activeAudioElement, latency / 1000)
    }
  }, [audio0Ref, audio1Ref, audioOffsetRef, nowPlayingData])
}
