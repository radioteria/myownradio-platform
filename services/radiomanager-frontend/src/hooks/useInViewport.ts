import { MutableRefObject, useEffect } from 'react'

export function useInViewport(elementRef: MutableRefObject<HTMLElement | null>) {
  useEffect(() => {
    const current = elementRef.current

    if (current === null) {
      return
    }

    const checkAndFixPosition = () => {
      const rect = current.getBoundingClientRect()

      // Check if the element is outside the viewport and adjust its position
      if (rect.bottom > window.innerHeight) {
        current.style.top = `${window.innerHeight - rect.height - 10}px`
      }

      if (rect.top < 0) {
        current.style.top = '0px'
      }

      if (rect.right > window.innerWidth) {
        current.style.left = `${window.innerWidth - rect.width - 10}px`
      }

      if (rect.left < 0) {
        current.style.left = '0px'
      }
    }

    window.addEventListener('scroll', checkAndFixPosition)
    window.addEventListener('resize', checkAndFixPosition)

    // Initial check
    checkAndFixPosition()

    return () => {
      window.removeEventListener('scroll', checkAndFixPosition)
      window.removeEventListener('resize', checkAndFixPosition)
    }
  }, [elementRef])
}
