import { useRef } from 'react'
import { useChannelPlayer } from './hooks/useChannelPlayer'

export const ChannelAudioPlayer = () => {
  const audio1Ref = useRef(null)
  const audio2Ref = useRef(null)
  const audioOffsetRef = useRef(0)

  useChannelPlayer(audio1Ref, audio2Ref, audioOffsetRef)

  return (
    <>
      <audio ref={audio1Ref} />
      <audio ref={audio2Ref} />
    </>
  )
}
