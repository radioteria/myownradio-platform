'use client'

import React from 'react'
import { UserEventProvider } from '@/context/UserEventProvider'
import { PlayerOverlay } from '@/views/PlayerPage/PlayerOverlay'
import { NowPlayingProvider } from '@/modules/NowPlaying'

interface Props {
  readonly channelId: number
}

export const PlayerPage: React.FC<Props> = ({ channelId }) => {
  return (
    <UserEventProvider>
      <NowPlayingProvider channelId={channelId}>
        <div className={'h-screen w-screen'}>
          <PlayerOverlay channelId={channelId} muted={false} />
        </div>
      </NowPlayingProvider>
    </UserEventProvider>
  )
}
