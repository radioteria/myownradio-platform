import { useEffect, useState } from 'react'
import { clamp, scale } from '@/utils/math'

interface Props {
  timestamp: number
  position: number
  duration: number
}

const ANIMATION_DURATION = 250

export const ProgressOverlay: React.FC<Props> = ({ timestamp, position, duration }) => {
  const [percent, setPercent] = useState(scale(position, duration, 100))

  useEffect(() => {
    let intervalId = window.setInterval(() => {
      const reactivePosition = Date.now() - timestamp + position
      setPercent(scale(clamp(0, reactivePosition, duration), duration, 100))
    }, ANIMATION_DURATION)

    return () => {
      window.clearInterval(intervalId)
    }
  }, [position, timestamp, duration])

  return (
    <div className={'h-full w-full'}>
      <div
        className={'h-full bg-slate-600'}
        style={{
          width: `${percent}%`,
          transitionProperty: 'width',
          transitionDuration: `${ANIMATION_DURATION}ms`,
          transitionTimingFunction: 'linear',
        }}
      />
    </div>
  )
}
