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

    if (current.length === 0 || hasTriggeredRef.current) return

    const handleIntersection = (entries: IntersectionObserverEntry[]) => {
      if (hasTriggeredRef.current) return

      for (const entry of entries) {
        if (entry.isIntersecting) {
          hasTriggeredRef.current = true
          onReach()
          break
        }
      }
    }

    const observer = new IntersectionObserver(handleIntersection, {
      threshold: 1,
    })

    for (const ref of current) {
      if (ref === null) continue
      observer.observe(ref)
    }

    return () => {
      for (const ref of current) {
        if (ref === null) continue
        observer.unobserve(ref)
      }
    }
  }, [onReach])

  return Children.map(children, (child, index) => {
    return <div ref={(el) => (childRefs.current[index] = el)}>{child}</div>
  })
}
