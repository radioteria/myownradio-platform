import { MutableRefObject } from 'react'
import { useAudioPlayerControl } from './useAudioPlayerControl'
import { useAudioPlayerSync } from '@/modules/ChannelAudioPlayer/hooks/useAudioPlayerSync'

export const useChannelAudioPlayer = (audioRef: MutableRefObject<HTMLAudioElement | null>) => {
  const { currentAudioOffsetRef, restart } = useAudioPlayerControl(audioRef)

  useAudioPlayerSync(audioRef, currentAudioOffsetRef, restart)
}
