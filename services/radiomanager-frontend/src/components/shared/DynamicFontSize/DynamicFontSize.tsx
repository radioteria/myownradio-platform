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
    if (!containerRef.current) return

    setContainerSize({
      width: containerRef.current.clientWidth,
      height: containerRef.current.clientHeight,
    })
  }, [])

  return (
    <div
      className={cn(`w-full h-full`, className)}
      style={{ fontSize: formula(containerSize) }}
      ref={containerRef}
      onResize={(event) =>
        setContainerSize({
          width: event.currentTarget.clientWidth,
          height: event.currentTarget.clientHeight,
        })
      }
    >
      {children}
    </div>
  )
}
