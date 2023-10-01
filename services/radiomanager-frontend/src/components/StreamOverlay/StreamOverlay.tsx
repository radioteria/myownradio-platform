'use client'

import { useState } from 'react'
import cn from 'classnames'
import { StreamPlayer } from '@/components/StreamPlayer'
import { DynamicFontSize } from '@/components/shared/DynamicFontSize/DynamicFontSize'

interface Props {
  readonly channelId: number
}

const Player: React.FC<Props> = ({ channelId }) => {
  const [title, setTitle] = useState('')

  return (
    <>
      <DynamicFontSize formula={({ width }) => `${width * 0.025}px`}>
        <div className={'absolute left-2 bottom-2 bg-morblue-800 px-2'}>{title}</div>
      </DynamicFontSize>

      <StreamPlayer channelId={channelId} onTrackChanged={setTitle} />
    </>
  )
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const [playing, setPlaying] = useState(false)

  return (
    <div
      onClick={() => setPlaying((playing) => !playing)}
      className={cn(['bg-black aspect-video text-white rounded-lg relative'])}
    >
      {!playing && (
        <DynamicFontSize
          className={'flex items-center justify-center'}
          formula={({ width }) => `${width * 0.05}px`}
        >
          NO SIGNAL
        </DynamicFontSize>
      )}
      {playing && <Player channelId={channelId} />}
    </div>
  )
}
