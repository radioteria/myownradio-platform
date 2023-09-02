'use client'

import { config } from '@/config'
import { useState } from 'react'
import cn from 'classnames'
import { ChannelAudioPlayer } from '@/modules/ChannelAudioPlayer'

interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const [playing, setPlaying] = useState(false)
  // TODO Connect to WS to listen channel events
  // TODO Connect to scheduler to get now-playing data
  // TODO Integrate audio player to listen to audio
  // const { nowPlaying } = useNowPlaying(channelId)

  return (
    <>
      <div
        onClick={() => setPlaying((playing) => !playing)}
        className={cn([
          'flex items-center justify-center',
          'bg-black aspect-video text-white rounded-lg',
        ])}
      >
        OFFLINE
      </div>
      {playing && <ChannelAudioPlayer />}
    </>
  )
}
