import { useIsVisible } from '@/hooks/useIsVisible'
import { useRef } from 'react'
import cn from 'classnames'

interface Props {
  size: number
}

export const GrowBar: React.FC<Props> = ({ size }) => {
  const ref = useRef(null)
  const isVisible = useIsVisible(ref)

  return (
    <>
      <svg
        ref={ref}
        width={size}
        style={{ display: 'inline' }}
        viewBox="0 0 15 16"
        xmlns="http://www.w3.org/2000/svg"
        fill={'currentcolor'}
      >
        <rect className={cn('bar1', { growBar: isVisible })} x="0" y="16" width="3" />
        <rect className={cn('bar2', { growBar: isVisible })} x="4" y="16" width="3" />
        <rect className={cn('bar3', { growBar: isVisible })} x="8" y="16" width="3" />
        <rect className={cn('bar4', { growBar: isVisible })} x="12" y="16" width="3" />
      </svg>
      <style jsx>{`
        .growBar {
          animation: growBar 0.35s infinite alternate;
          height: 100%;
        }

        .bar1 {
          animation-delay: 0ms;
        }

        .bar2 {
          animation-delay: 300ms;
        }

        .bar3 {
          animation-delay: 200ms;
        }

        .bar4 {
          animation-delay: 500ms;
        }

        @keyframes growBar {
          from {
            transform: translateY(0);
          }
          to {
            transform: translateY(-16px);
          }
        }
      `}</style>
    </>
  )
}

export default GrowBar
