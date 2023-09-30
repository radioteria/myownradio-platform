import { RefObject, useEffect, useState } from 'react'

export const useIsVisible = (ref: RefObject<HTMLElement>, threshold = 1) => {
  const [isVisible, setIsVisible] = useState(false)

  useEffect(() => {
    if (!ref.current) return

    const element = ref.current

    const observer = new IntersectionObserver(
      (entries) => {
        setIsVisible(entries.some((item) => item.isIntersecting))
      },
      { threshold },
    )

    observer.observe(element)

    return () => {
      observer.unobserve(element)
    }
  }, [threshold, ref])

  return isVisible
}
