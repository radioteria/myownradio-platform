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

interface CSSProperties extends React.CSSProperties {
  '--scale': number
}

export const ProgressOverlay: React.FC<Props> = ({ position, duration }) => {
  const animationDuration = duration - position
  const initialScale = (1 / duration) * position

  const style: CSSProperties = {
    '--scale': initialScale,
  }

  return (
    <div className={'h-full w-full'} style={style}>
      <style jsx>{widthKeyframes}</style>
      <div
        key={`k-${position}`}
        className={'h-full w-full origin-left bg-slate-600'}
        style={{
          animation: 'scale',
          animationDuration: `${animationDuration}ms`,
          animationTimingFunction: 'linear',
        }}
      />
    </div>
  )
}
