import { useEffect, useRef, useState } from 'react'
import cn from 'classnames'

type Size = { readonly width: number; readonly height: number }

export interface Props {
  readonly className?: string
  readonly formula: (containerSize: Size) => string
  readonly children: React.ReactNode
}

export const DynamicFontSize: React.FC<Props> = ({ className, formula, children }) => {
  const containerRef = useRef<HTMLDivElement>(null)
  const [containerSize, setContainerSize] = useState<Size>({
    width: 0,
    height: 0,
  })

  useEffect(() => {
    const handleResize = () => {
      if (containerRef.current) {
        setContainerSize({
          width: containerRef.current.clientWidth,
          height: containerRef.current.clientHeight,
        })
      }
    }

    window.addEventListener('resize', handleResize)

    handleResize()

    return () => {
      window.removeEventListener('resize', handleResize)
    }
  }, [])

  return (
    <div
      ref={containerRef}
      className={cn(className, 'w-full h-full')}
      style={{ fontSize: formula(containerSize) }}
    >
      {children}
    </div>
  )
}
