import React from 'react'

interface Props {
  position: number
  duration: number
  withProgressing: boolean
  onSeek: (position: number) => void
}

export const ProgressBar: React.FC<Props> = ({ position, duration, withProgressing, onSeek }) => {
  const animationDuration = duration - position
  const initialScale = (1 / duration) * position

  const handleClick = (event: React.MouseEvent<HTMLDivElement>) => {
    const seekPosition =
      (duration / event.currentTarget.clientWidth) *
      (event.clientX - event.currentTarget.offsetLeft)

    onSeek(Math.floor(seekPosition))
  }

  return (
    <div
      className={'h-2 bg-gray-400 rounded-md relative overflow-hidden flex-1 mx-2 cursor-pointer'}
      onClick={handleClick}
    >
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
