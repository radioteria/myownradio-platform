import React from 'react'

interface Props {
  position: number
  duration: number
  withProgressing: boolean
}

export const ProgressBar: React.FC<Props> = ({ position, duration, withProgressing }) => {
  const animationDuration = duration - position
  const initialScale = (1 / duration) * position

  return (
    <div className={'h-2 bg-gray-400 rounded-md relative overflow-hidden flex-1 mx-2'}>
      <style jsx>{`
        @keyframes scale {
          0% {
            transform: scale(${initialScale}, 1);
          }

          100% {
            transform: scale(1, 1);
          }
        }
      `}</style>
      <div
        key={`k-${position}`}
        className={'h-full w-full origin-left bg-morblue-300'}
        style={{
          animation: 'scale',
          animationDuration: `${animationDuration}ms`,
          animationTimingFunction: 'linear',
          animationPlayState: withProgressing ? 'running' : 'paused',
        }}
      />
    </div>
  )
}
