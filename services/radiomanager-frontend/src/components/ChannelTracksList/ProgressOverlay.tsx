import { useEffect, useState } from 'react'

interface Props {
  timestamp: number
  position: number
  duration: number
}

const SYNC_INTERVAL = 250

export const ProgressOverlay: React.FC<Props> = ({ timestamp, position, duration }) => {
  const [percent, setPercent] = useState(position)

  useEffect(() => {
    let intervalId = window.setInterval(() => {
      const now = Date.now()
      const reactivePosition = now - timestamp + position
      setPercent((100 / duration) * Math.min(reactivePosition, duration))
    }, SYNC_INTERVAL)

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
          transitionDuration: `${SYNC_INTERVAL}ms`,
          transitionTimingFunction: 'linear',
        }}
      />
    </div>
  )
}
