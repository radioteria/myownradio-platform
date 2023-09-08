import React, { useRef, useEffect, ReactNode, useState } from 'react'

interface InfiniteScrollProps {
  onReach: () => void
  children?: ReactNode
}

export const InfiniteScroll: React.FC<InfiniteScrollProps> = ({ onReach, children }) => {
  const ref = useRef<HTMLDivElement>(null)
  const [hasTriggered, setHasTriggered] = useState(false)

  // Intersection
  useEffect(() => {
    const current = ref.current

    if (!current || hasTriggered) return

    const handleIntersection = (entries: IntersectionObserverEntry[]) => {
      const entry = entries[0]

      if (entry.isIntersecting) {
        onReach()
        setHasTriggered(true)
      }
    }

    const observer = new IntersectionObserver(handleIntersection, {
      threshold: 0.25,
    })

    observer.observe(current)

    return () => observer.unobserve(current)
  }, [hasTriggered, onReach])

  return <div ref={ref}>{children}</div>
}
