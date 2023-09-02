import { MutableRefObject, useEffect, useRef } from 'react'
import makeDebug from 'debug'
import { useNowPlaying } from '@/modules/NowPlaying'
import { loadAudio, playAudio, stopAudio } from '@/utils/audio'
import { BACKEND_BASE_URL } from '@/api'
import { filterBelow, scale } from '@/utils/math'

const debug = makeDebug('useChannelPlayer')

const START_TOLERANCE = 500

export const useChannelPlayer = (
  audio1Ref: MutableRefObject<HTMLAudioElement | null>,
  audio2Ref: MutableRefObject<HTMLAudioElement | null>,
  audioOffsetRef: MutableRefObject<number>,
) => {
  const nowPlayingData = useNowPlaying()
  const currentTrackId = nowPlayingData.nowPlaying?.currentTrack.trackId ?? null

  const isPlaying = !!nowPlayingData.nowPlaying
  const activeAudioRef = useRef(0)

  // Play Hook
  useEffect(() => {
    const audio1Element = audio1Ref.current
    const audio2Element = audio2Ref.current
    const { nowPlaying, updatedAt } = nowPlayingData

    if (!audio1Element || !audio2Element || !nowPlaying) return

    activeAudioRef.current = 1 - activeAudioRef.current
    const activeAudioElement = activeAudioRef.current === 0 ? audio1Element : audio2Element
    const inactiveAudioElement = activeAudioRef.current === 0 ? audio2Element : audio1Element
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
  }, [audio1Ref, audioOffsetRef, audio2Ref, currentTrackId])

  // Stop Hook
  useEffect(() => {
    const audio1Element = audio1Ref.current
    const audio2Element = audio2Ref.current

    if (!isPlaying || !audio1Element || !audio2Element) return

    return () => {
      debug('Stopping audio playback')
      stopAudio(audio1Element)
      stopAudio(audio2Element)
    }
  }, [audio1Ref, audio2Ref, isPlaying])
}
