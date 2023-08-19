import { scale } from '@/utils/math'
import css from 'styled-jsx/css'
import React from 'react'

interface Props {
  position: number
  duration: number
}

const widthKeyframes = css`
  @keyframes scale {
    0% {
      transform: scale(var(--scale), 1);
    }

    100% {
      transform: scale(1, 1);
    }
  }
`

export const ProgressOverlay: React.FC<Props> = ({ position, duration }) => {
  const animationDuration = duration - position
  const initialScale = (1 / duration) * position

  return (
    <div className={'h-full w-full'}>
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
        className={'h-full w-full origin-left bg-morblue-800'}
        style={{
          animation: 'scale',
          animationDuration: `${animationDuration}ms`,
          animationTimingFunction: 'linear',
        }}
      />
    </div>
  )
}
