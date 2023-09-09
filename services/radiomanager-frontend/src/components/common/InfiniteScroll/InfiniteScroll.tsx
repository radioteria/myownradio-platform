import React, { useRef, useEffect, ReactNode, useState } from 'react'

interface InfiniteScrollProps {
  onReach: () => void
  children?: ReactNode
  threshold?: number
}

export const InfiniteScroll: React.FC<InfiniteScrollProps> = ({
  onReach,
  children,
  threshold = 0,
}) => {
  const ref = useRef<HTMLDivElement>(null)
  const [hasTriggered, setHasTriggered] = useState(false)

  // Intersection
  useEffect(() => {
    const current = ref.current

    if (!current || hasTriggered) return

    const handleIntersection = ([entry]: IntersectionObserverEntry[]) => {
      if (entry.isIntersecting && entry.intersectionRatio) {
        onReach()
        setHasTriggered(true)
      }
    }

    const observer = new IntersectionObserver(handleIntersection, {
      threshold,
    })

    observer.observe(current)

    return () => observer.unobserve(current)
  }, [hasTriggered, onReach, threshold])

  return <div ref={ref}>{children}</div>
}
