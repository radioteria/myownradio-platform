import { useRef } from 'react'
import { useChannelPlayer } from './hooks/useChannelPlayer'

interface Props {
  readonly channelId: number
}

export const ChannelAudioPlayer: React.FC<Props> = ({ channelId }) => {
  const audio1Ref = useRef(null)

  useChannelPlayer(channelId, audio1Ref)

  return (
    <>
      <audio ref={audio1Ref} />
    </>
  )
}
