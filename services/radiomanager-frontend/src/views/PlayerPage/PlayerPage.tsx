'use client'

import { StreamOverlay } from '@/views/ChannelPage/ChannelTracksList/ChannelControls/StreamOverlay'
import React from 'react'
import { UserEventProvider } from '@/context/UserEventProvider'

interface Props {
  readonly channelId: number
}

export const PlayerPage: React.FC<Props> = ({ channelId }) => {
  return (
    <UserEventProvider>
      <StreamOverlay channelId={channelId} />
    </UserEventProvider>
  )
}
