import { RefObject, useEffect } from 'react'
import { ChannelPlayerService } from '@/modules/ChannelAudioPlayer/ChannelPlayerService'

export const useChannelPlayer2 = (
  channelId: number,
  audio0Ref: RefObject<HTMLAudioElement | null>,
) => {
  useEffect(() => {
    if (!audio0Ref.current) return

    const player = new ChannelPlayerService(channelId, audio0Ref.current)

    player.runLoop().catch((error) => {
      console.error(error)
    })

    return () => {
      player.stop()
    }
  }, [channelId, audio0Ref])
}
