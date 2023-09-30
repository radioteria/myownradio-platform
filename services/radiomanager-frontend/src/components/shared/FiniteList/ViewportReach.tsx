import React, { Children, useRef, useEffect, ReactNode } from 'react'

interface Props {
  onReach: () => void
  children?: ReactNode
}

export const ViewportReach: React.FC<Props> = ({ onReach, children }) => {
  const childRefs = useRef<(Element | null)[]>([])
  const hasTriggeredRef = useRef(false)

  // Intersection
  useEffect(() => {
    const current = [...childRefs.current]

    if (current.length === 0) return

    const handleIntersection = (entries: IntersectionObserverEntry[]) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          if (!hasTriggeredRef.current) {
            onReach()
            hasTriggeredRef.current = true
          }
          return
        }
      }
    }

    const observer = new IntersectionObserver(handleIntersection, {
      threshold: 0.5,
    })

    for (const ref of current) {
      if (ref === null) continue
      observer.observe(ref)
    }

    return () => {
      observer.disconnect()
    }
  }, [onReach])

  return Children.map(children, (child, index) => {
    return <div ref={(el) => (childRefs.current[index] = el)}>{child}</div>
  })
}
