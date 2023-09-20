import React, {
  Children,
  useRef,
  useEffect,
  ReactNode,
  useState,
  cloneElement,
  isValidElement,
} from 'react'

interface Props {
  onReach: () => void
  children?: ReactNode
}

export const ViewportReach: React.FC<Props> = ({ onReach, children }) => {
  const childRefs = useRef<(Element | null)[]>([])
  const [hasTriggered, setHasTriggered] = useState(false)

  // Intersection
  useEffect(() => {
    const current = [...childRefs.current]

    if (current.length === 0 || hasTriggered) return

    const handleIntersection = (entries: IntersectionObserverEntry[]) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          onReach()
          setHasTriggered(true)
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
  }, [hasTriggered, onReach])

  return Children.map(children, (child, index) => {
    return <div ref={(el) => (childRefs.current[index] = el)}>{child}</div>
  })
}
