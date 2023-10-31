import cn from 'classnames'
import React from 'react'

interface Props {
  status: 'preview' | 'live'
  overlay: React.ReactNode
}

export const StreamInfoBar: React.FC<Props> = ({ overlay, status }) => {
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
      </div>
    </div>
  )
}
