import { useRef } from 'react'
import { useChannelAudioPlayer } from './hooks/useChannelAudioPlayer'

export const ChannelAudioPlayer = () => {
  const audioRef = useRef(null)

  useChannelAudioPlayer(audioRef)

  return <audio ref={audioRef} />
}
