import { Children, cloneElement, isValidElement, useEffect, useRef, useState } from 'react'

type StyleableElement = React.ReactElement<{ style?: React.CSSProperties }>
type Size = { readonly width: number; readonly height: number }

export interface Props {
  readonly formula: (containerSize: Size) => string
  readonly children: StyleableElement
}

export const DynamicFontSize: React.FC<Props> = ({ formula, children }) => {
  const containerRef = useRef<HTMLDivElement>(null)
  const [containerSize, setContainerSize] = useState<Size>({
    width: 0,
    height: 0,
  })

  useEffect(() => {
    if (!containerRef.current) return

    setContainerSize({
      width: containerRef.current.getBoundingClientRect().width,
      height: containerRef.current.getBoundingClientRect().height,
    })
  }, [])

  return (
    <div
      className={'w-full h-full'}
      ref={containerRef}
      onResize={(event) =>
        setContainerSize({
          width: event.currentTarget.clientWidth,
          height: event.currentTarget.clientHeight,
        })
      }
    >
      {Children.map(children, (child) => {
        return isValidElement(child)
          ? cloneElement(child, {
              style: {
                ...child.props.style,
                fontSize: formula(containerSize),
              },
            })
          : child
      })}
    </div>
  )
}
