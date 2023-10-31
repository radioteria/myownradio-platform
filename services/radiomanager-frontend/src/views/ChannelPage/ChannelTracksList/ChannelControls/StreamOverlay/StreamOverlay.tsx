'use client'

import { useState } from 'react'
import cn from 'classnames'
import { PlayerOverlay } from '@/views/PlayerPage/PlayerOverlay'
import { MuteIcon } from './icons/MuteIcon'
import { UnmuteIcon } from './icons/UnmuteIcon'

interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const [playing, setPlaying] = useState(false)

  return (
    <div className={'relative'}>
      <div className={cn(['bg-black aspect-video text-white rounded-lg overflow-hidden'])}>
        <PlayerOverlay muted={!playing} channelId={channelId} />
      </div>
      <div className={'absolute bottom-0 left-0 w-full flex space-x-1 justify-end pb-2 pr-2'}>
        {playing ? (
          <button
            title={'Mute'}
            onClick={() => setPlaying(false)}
            className={
              'grow-0 bg-black bg-opacity-50 transition hover:bg-opacity-75 w-8 rounded-md'
            }
          >
            <UnmuteIcon size={'100%'} />
          </button>
        ) : (
          <button
            title={'Unmute'}
            onClick={() => setPlaying(true)}
            className={
              'grow-0 bg-black bg-opacity-50 transition hover:bg-opacity-75 w-8 rounded-md'
            }
          >
            <MuteIcon size={'100%'} />
          </button>
        )}
      </div>
    </div>
  )
}
