import { useRef } from 'react'
import { useChannelPlayer } from './hooks/useChannelPlayer'
import { useChannelPlayer2 } from './hooks/useChannelPlayer2'

interface Props {
  readonly channelId: number
}

export const ChannelAudioPlayer: React.FC<Props> = ({ channelId }) => {
  const audio1Ref = useRef(null)
  const audio2Ref = useRef(null)
  const audioOffsetRef = useRef(0)

  useChannelPlayer2(channelId, audio1Ref, audio2Ref)

  return (
    <>
      <audio ref={audio1Ref} />
      <audio ref={audio2Ref} />
    </>
  )
}
