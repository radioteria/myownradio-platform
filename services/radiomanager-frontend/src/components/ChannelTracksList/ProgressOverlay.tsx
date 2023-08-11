import { scale } from '@/utils/math'
import css from 'styled-jsx/css'
import React from 'react'

interface Props {
  position: number
  duration: number
}

const widthKeyframes = css`
  @keyframes width {
    0% {
      width: var(--width);
    }

    100% {
      width: 100%;
    }
  }
`

interface CSSProperties extends React.CSSProperties {
  '--width': string
}

export const ProgressOverlay: React.FC<Props> = ({ position, duration }) => {
  const timeToAnimate = duration - position
  const initialPositionPercent = scale(position, duration, 100)

  const style: CSSProperties = {
    '--width': `${initialPositionPercent}%`,
  }

  return (
    <div className={'h-full w-full'} style={style}>
      <style jsx>{widthKeyframes}</style>
      <div
        key={`k-${position}`}
        className={'h-full bg-slate-600'}
        style={{
          animation: 'width',
          animationDuration: `${timeToAnimate}ms`,
          animationTimingFunction: 'linear',
        }}
      />
    </div>
  )
}
