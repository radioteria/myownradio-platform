import React, { useRef, useEffect, ReactNode, useState } from 'react'

interface InfiniteScrollProps {
  onReach: () => void
  offset?: number
  children?: ReactNode
}

export const InfiniteScroll: React.FC<InfiniteScrollProps> = ({
  onReach,
  offset = 100,
  children,
}) => {
  const ref = useRef<HTMLDivElement>(null)
  const [hasTriggered, setHasTriggered] = useState(false)

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

    const observer = new IntersectionObserver(handleIntersection)

    observer.observe(current)

    return () => observer.unobserve(current)
  }, [onReach, offset, hasTriggered])

  return <div ref={ref}>{children}</div>
}
