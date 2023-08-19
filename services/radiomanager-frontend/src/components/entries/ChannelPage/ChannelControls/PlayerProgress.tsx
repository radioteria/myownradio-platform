import React, { ReactNode, useEffect, useMemo, useState } from 'react'
import { Duration } from '@/components/Duration'

interface Props {
  position: number
  duration: number
  progressBar: ReactNode
}

export const PlayerProgress: React.FC<Props> = ({ position, duration, progressBar }) => {
  const updatedAt = useMemo(() => Date.now(), [position])
  const [positionProgress, setPositionProgress] = useState(position)

  useEffect(() => {
    const intervalId = window.setInterval(() => {
      setPositionProgress(position + (Date.now() - updatedAt))
    })

    return () => {
      window.clearInterval(intervalId)
    }
  }, [updatedAt, position])

  return (
    <div className={'flex w-full items-center'}>
      <div className={'flex-shrink-0 text-xs w-9 text-right'}>
        <Duration millis={positionProgress} />
      </div>
      {progressBar}
      <div className={'flex-shrink-0 text-xs w-9 text-left'}>
        <Duration millis={duration} />
      </div>
    </div>
  )
}
