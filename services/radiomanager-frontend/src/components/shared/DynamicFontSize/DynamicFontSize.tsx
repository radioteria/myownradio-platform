import { useEffect, useRef, useState } from 'react'
import cn from 'classnames'

type Size = { readonly width: number; readonly height: number }

export interface Props {
  readonly className?: string
  readonly formula: (containerSize: Size) => string
  readonly children: React.ReactNode
}

export const DynamicFontSize: React.FC<Props> = ({ className, formula, children }) => {
  const [containerSize, setContainerSize] = useState<Size>({
    width: 0,
    height: 0,
  })

  useEffect(() => {
    const handleResize = () => {
      setContainerSize({
        width: window.innerWidth,
        height: window.innerHeight,
      })
    }

    window.addEventListener('resize', handleResize)

    handleResize()

    return () => {
      window.removeEventListener('resize', handleResize)
    }
  }, [])

  return (
    <span className={className} style={{ fontSize: formula(containerSize) }}>
      {children}
    </span>
  )
}
