import { RefObject, useEffect } from 'react'
import { ChannelPlayerService } from '@/modules/ChannelAudioPlayer/ChannelPlayerService'
import makeDebug from 'debug'

const debug = makeDebug('useChannelPlayer2')

export const useChannelPlayer2 = (
  channelId: number,
  audio0Ref: RefObject<HTMLAudioElement | null>,
  audio1Ref: RefObject<HTMLAudioElement | null>,
) => {
  useEffect(() => {
    if (!audio0Ref.current || !audio1Ref.current) return

    const player = new ChannelPlayerService(channelId, audio0Ref.current, audio1Ref.current)

    player.runLoop().catch((error) => {
      debug('Player loop exited with error: %s', error)
    })

    return () => {
      player.stop()
    }
  }, [channelId, audio0Ref])
}
