import cn from 'classnames'
import { MuteIcon } from '@/views/ChannelPage/ChannelTracksList/ChannelControls/StreamInfoBar/icons/MuteIcon'
import React from 'react'
import { UnmuteIcon } from '@/views/ChannelPage/ChannelTracksList/ChannelControls/StreamInfoBar/icons/UnmuteIcon'

interface Props {
  status: 'preview' | 'live'
  overlay: React.ReactNode
  muted: boolean
  setMuted: (muted: boolean) => void
}

export const StreamInfoBar: React.FC<Props> = ({ overlay, status, muted, setMuted }) => {
  const handleMuteToggle = () => setMuted(!muted)

  return (
    <div className={cn('rounded-lg', status === 'preview' ? 'bg-morblue-1000' : 'bg-red-500')}>
      {overlay}

      <div className={'flex justify-center relative'}>
        <div
          className={cn(
            'py-1 px-2 font-bold text-sm',
            status === 'preview' ? 'text-gray-400' : 'text-gray-950',
          )}
        >
          {status === 'preview' ? 'PREVIEW' : 'LIVE'}
        </div>
        <div className={'absolute right-0'}>
          <button onClick={handleMuteToggle} aria-label={'Toggle mute'}>
            {muted ? <MuteIcon size={22} /> : <UnmuteIcon size={22} />}
          </button>
        </div>
      </div>
    </div>
  )
}
