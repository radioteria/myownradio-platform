'use client'

import { useState } from 'react'
import cn from 'classnames'
import { StreamPlayer } from '@/components/StreamPlayer'

interface Props {
  readonly channelId: number
}

const Player: React.FC<Props> = ({ channelId }) => {
  const [title, setTitle] = useState('')

  return (
    <>
      <div className={'absolute left-2 bottom-2 bg-morblue-800 px-2'}>{title}</div>
      <StreamPlayer channelId={channelId} onTrackChanged={setTitle} />
    </>
  )
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const [playing, setPlaying] = useState(false)

  return (
    <div
      onClick={() => setPlaying((playing) => !playing)}
      className={cn([
        'flex items-center justify-center',
        'bg-black aspect-video text-white rounded-lg relative',
      ])}
    >
      {!playing && 'NO SIGNAL'}
      {playing && <Player channelId={channelId} />}
    </div>
  )
}
