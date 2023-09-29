import { useRef } from 'react'
import { useChannelPlayer } from './hooks/useChannelPlayer'

interface Props {
  readonly channelId: number
}

export const ChannelAudioPlayer: React.FC<Props> = ({ channelId }) => {
  const audioRef = useRef(null)

  useChannelPlayer(channelId, audioRef)

  return <audio ref={audioRef} />
}
