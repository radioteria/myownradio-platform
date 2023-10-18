'use client'

import { StreamOverlay } from '@/views/ChannelPage/ChannelTracksList/ChannelControls/StreamOverlay'
import React from 'react'
import { UserEventProvider } from '@/context/UserEventProvider'
import { PlayerOverlay } from '@/views/PlayerPage/PlayerOverlay'

interface Props {
  readonly channelId: number
}

export const PlayerPage: React.FC<Props> = ({ channelId }) => {
  return (
    <UserEventProvider>
      <PlayerOverlay channelId={channelId} muted={false} />
    </UserEventProvider>
  )
}
