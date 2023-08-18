import { MutableRefObject, useEffect, useState } from 'react'

interface Position {
  x: number
  y: number
}

export function useMenuPosition(
  menuElementRef: MutableRefObject<HTMLElement | null>,
  preferredPosition: Position,
): Position {
  const [position, setPosition] = useState<Position>(preferredPosition)

  useEffect(() => {
    const current = menuElementRef.current

    if (current === null) {
      return
    }

    const checkAndFixPosition = () => {
      const rect = current.getBoundingClientRect()
      const position: Position = { ...preferredPosition }

      if (rect.bottom > window.innerHeight) {
        position.y -= rect.height
      }

      if (rect.right > window.innerWidth) {
        position.x -= rect.width
      }

      if (rect.top < 0) {
        position.y = 0
      }

      if (rect.left < 0) {
        position.x = 0
      }

      setPosition((prevPosition) => {
        if (!prevPosition || prevPosition.x !== position.x || prevPosition.y !== position.y) {
          return position
        }

        return prevPosition
      })
    }

    window.addEventListener('scroll', checkAndFixPosition)
    window.addEventListener('resize', checkAndFixPosition)

    // Initial check
    checkAndFixPosition()

    return () => {
      window.removeEventListener('scroll', checkAndFixPosition)
      window.removeEventListener('resize', checkAndFixPosition)
    }
  }, [menuElementRef, preferredPosition])

  return position
}
