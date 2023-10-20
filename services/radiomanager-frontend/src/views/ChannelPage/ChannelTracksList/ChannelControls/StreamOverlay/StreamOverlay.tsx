'use client'

import { useState } from 'react'
import cn from 'classnames'
import { DynamicFontSize } from '@/components/shared/DynamicFontSize/DynamicFontSize'
import { PlayerOverlay } from '@/views/PlayerPage/PlayerOverlay'

interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const [playing, setPlaying] = useState(false)

  return (
    <div
      onClick={() => setPlaying((playing) => !playing)}
      className={cn(['bg-black aspect-video text-white rounded-lg relative overflow-hidden'])}
    >
      {!playing && (
        <DynamicFontSize
          className={cn(
            'flex items-center justify-center',
            'w-full h-full l-0 t-0 absolute z-10',
            'cursor-pointer bg-black bg-opacity-50',
          )}
          formula={({ width }) => `${width * 0.05}px`}
        >
          Click to preview
        </DynamicFontSize>
      )}
      <PlayerOverlay muted={!playing} channelId={channelId} />
    </div>
  )
}
