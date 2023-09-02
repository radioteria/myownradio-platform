import { MutableRefObject } from 'react'
import { usePlayStopAudio } from './usePlayStopAudio'
import { useSeekAudio } from '@/modules/ChannelAudioPlayer/hooks/useSeekAudio'

export const useChannelAudioPlayer = (audioRef: MutableRefObject<HTMLAudioElement | null>) => {
  const currentAudioOffsetRef = usePlayStopAudio(audioRef)

  useSeekAudio(audioRef, currentAudioOffsetRef)
}
