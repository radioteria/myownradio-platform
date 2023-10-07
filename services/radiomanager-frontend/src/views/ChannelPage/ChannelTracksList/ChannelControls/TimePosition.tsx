import React, { ReactNode, useEffect, useMemo, useState } from 'react'
import { Duration } from '@/components/shared/Duration'

interface Props {
  position: number
  duration: number
  withProgressing: boolean
  progressBar: ReactNode
}

export const TimePosition: React.FC<Props> = ({
  position,
  duration,
  withProgressing,
  progressBar,
}) => {
  const updatedAt = useMemo(() => Date.now(), [position])
  const [positionProgress, setPositionProgress] = useState(position)

  useEffect(() => {
    if (!withProgressing) return

    const intervalId = window.setInterval(() => {
      setPositionProgress(position + (Date.now() - updatedAt))
    })

    return () => {
      window.clearInterval(intervalId)
    }
  }, [updatedAt, position, withProgressing])

  return (
    <div className={'flex w-full items-center'}>
      <div className={'flex-shrink-0 w-7 text-right'}>
        <Duration millis={positionProgress} />
      </div>
      {progressBar}
      <div className={'flex-shrink-0 w-7 text-left'}>
        <Duration millis={duration} />
      </div>
    </div>
  )
}
