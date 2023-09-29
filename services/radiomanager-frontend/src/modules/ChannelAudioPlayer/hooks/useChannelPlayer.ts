import { RefObject, useEffect } from 'react'
import { Player } from '@/player/player'
import makeDebug from 'debug'

const debug = makeDebug('useChannelPlayer')

export const useChannelPlayer = (
  channelId: number,
  audio0Ref: RefObject<HTMLAudioElement | null>,
) => {
  useEffect(() => {
    if (!audio0Ref.current) return

    const player = new Player(channelId, audio0Ref.current)

    player.runLoop().catch((error) => {
      debug('Player loop exited with error: %s', error)
    })

    return () => {
      player.stop()
    }
  }, [channelId, audio0Ref])
}
